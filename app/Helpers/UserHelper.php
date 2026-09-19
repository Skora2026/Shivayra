<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class UserHelper
{
    private static $authClass = Auth::class;

    /**
     * function to get the logged-in user data
     *
     * @return object
     */
    public static function getLoggedInUser()
    {
        $userData = null;
        if (self::$authClass::check()) {
            $userData = self::$authClass::user();
        }

        return $userData;
    }

    /**
     * function to upload the profile photo
     *
     * @param  mixed  $fileData
     * @return string
     */
    public static function uploadImages($fileData, $destinationPath)
    {
        if (! Storage::disk('public')->exists($destinationPath)) {
            Storage::disk('public')->makeDirectory($destinationPath);
        }

        return $fileData->store($destinationPath, 'public');
    }

    /**
     * function to delete the uploaded file
     *
     * @param  object  $user
     * @return void
     */
    public static function deleteImages($directory, $filename)
    {
        $filePath = $directory.'/'.$filename;

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }

        return false;
    }

    /**
     * Retrieve the currently selected project ID from the session.
     *
     * This method returns the value of 'project_id' stored in the user's session.
     * It is typically used to determine which project the user is actively working on.
     *
     * @return mixed|null Returns the project ID if set, or null if not found in the session.
     */
    public static function getSelectedProjectId()
    {
        return Session::get('project_id');
    }

    /**
     * Convert a number to Indian currency format.
     *
     * @param  float  $number  The number to convert.
     * @return string The formatted currency string.
     */
    public static function convertToIndianCurrency($number)
    {
        $no = floor($number);
        $decimal = round($number - $no, 2) * 100; // paise
        $digits_length = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0 => '',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
        ];
        $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number_part = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number_part) {
                $plural = (($counter = count($str)) && $number_part > 9) ? '' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number_part < 21) ? $words[$number_part].' '.$digits[$counter].$plural.' '.$hundred
                    : $words[floor($number_part / 10) * 10].' '.$words[$number_part % 10].' '.$digits[$counter].$plural.' '.$hundred;
            } else {
                $str[] = null;
            }
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($decimal) ?
            ' and '.$words[floor($decimal / 10) * 10].' '.$words[$decimal % 10].' paise' : '';

        return ucfirst($result).'rupees'.$points.' only';
    }
}
