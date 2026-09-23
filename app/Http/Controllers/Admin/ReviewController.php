<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * All customer reviews with product and author context.
     */
    public function index(Request $request)
    {
        $query = ProductReview::with(['product:id,name,slug', 'user:id,name', 'order:id,order_number']);

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }
        if ($request->filled('status')) {
            $query->where('is_approved', $request->status === 'approved');
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => ProductReview::count(),
            'pending' => ProductReview::where('is_approved', false)->count(),
            'avg' => round((float) ProductReview::avg('rating'), 2),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Approve / unapprove a review.
     */
    public function updateApproval(Request $request, ProductReview $review)
    {
        $data = $request->validate(['is_approved' => 'required|boolean']);
        $review->update(['is_approved' => $data['is_approved']]);

        return redirect()->back()->with(
            'success',
            'Review '.($data['is_approved'] ? 'approved and visible' : 'hidden from').' the product page.'
        );
    }

    /**
     * Delete an inappropriate review outright.
     */
    public function destroy(ProductReview $review)
    {
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted.');
    }
}
