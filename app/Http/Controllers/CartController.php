<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Server-side cart for logged-in customers.
 *
 * The client keeps working unchanged (localStorage) and pushes its state
 * here on every mutation. On login, the client's local list is merged into
 * the server rows so the cart follows the user across devices and survives
 * cache clears. Prices are never trusted from the client — checkout already
 * re-computes them from the database.
 */
class CartController extends Controller
{
    /** Validated client cart shape. */
    private function normalizeItems(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id']) || ! is_numeric($item['qty'])) {
                continue;
            }
            $qty = max(1, min(999, (int) $item['qty']));
            $variantId = isset($item['variantId']) && $item['variantId'] !== '' && $item['variantId'] !== null
                ? (int) $item['variantId']
                : null;

            $out[$this->key($item['id'], $variantId)] = [
                'product_id' => (int) $item['id'],
                'product_variant_id' => $variantId,
                'qty' => $qty,
            ];
        }

        return $out;
    }

    private function key($productId, $variantId): string
    {
        return $productId.'|'.($variantId ?? 0);
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            // 'present' (not 'required'): an EMPTY array is a legitimate payload —
            // it means "the customer emptied their cart" and must clear the server
            // rows. 'required' rejects [] with a 422, which would let the cart
            // resurrect on the next device.
            'items' => 'present|array|max:100',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1|max:999',
            'items.*.variantId' => 'nullable',
            'merge' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $incoming = $this->normalizeItems($data['items']);
        $merge = (bool) ($data['merge'] ?? false);

        if ($merge) {
            // Login-merge semantics: ADD the client's pre-login items into the
            // existing server rows (quantities summed, nothing shrunk/deleted)
            // so rows added on another device survive the merge.
            foreach ($incoming as $row) {
                if ($this->rowIsValid($row)) {
                    $existing = CartItem::where('user_id', $user->id)
                        ->where('product_id', $row['product_id'])
                        ->where('product_variant_id', $row['product_variant_id'])
                        ->first();
                    if ($existing) {
                        $existing->qty = min(999, $existing->qty + $row['qty']);
                        $existing->save();
                    } else {
                        CartItem::create([
                            'user_id' => $user->id,
                            'product_id' => $row['product_id'],
                            'product_variant_id' => $row['product_variant_id'],
                            'qty' => $row['qty'],
                        ]);
                    }
                }
            }

            return response()->json(['ok' => true, 'count' => CartItem::where('user_id', $user->id)->sum('qty')]);
        }

        // Mirror semantics: the client is the source of truth for its own cart.
        // Prune by EXACT key (product + variant): any row the client no longer
        // has is deleted. An empty incoming list clears everything — important,
        // because whereNotIn('product_id', []) compiles to 0 = 1 and would
        // silently no-op, letting an emptied cart resurrect on the next device.
        $keep = array_keys($incoming); // "product|variant" keys
        CartItem::where('user_id', $user->id)
            ->get()
            ->filter(fn (CartItem $row) => ! in_array($this->key($row->product_id, $row->product_variant_id), $keep, true))
            ->each(fn (CartItem $row) => $row->delete());

        foreach ($incoming as $row) {
            if ($this->rowIsValid($row)) {
                CartItem::updateOrCreate(
                    ['user_id' => $user->id, 'product_id' => $row['product_id'], 'product_variant_id' => $row['product_variant_id']],
                    ['qty' => $row['qty']]
                );
            }
        }

        return response()->json(['ok' => true, 'count' => CartItem::where('user_id', $user->id)->sum('qty')]);
    }

    /** Shared existence checks for a normalized incoming row. */
    private function rowIsValid(array $row): bool
    {
        if (! Product::where('id', $row['product_id'])->exists()) {
            return false;
        }
        if ($row['product_variant_id'] !== null
            && ! ProductVariant::where('id', $row['product_variant_id'])->where('product_id', $row['product_id'])->exists()) {
            return false;
        }

        return true;
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer',
            'qty' => 'required|integer|min:1|max:999',
        ]);

        $variantId = $data['variant_id'] ?? null;
        if ($variantId !== null
            && ! ProductVariant::where('id', $variantId)->where('product_id', $data['product_id'])->exists()) {
            $variantId = null;
        }

        CartItem::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $data['product_id'],
                'product_variant_id' => $variantId,
            ],
            ['qty' => $data['qty']]
        );

        return response()->json(['ok' => true, 'count' => CartItem::where('user_id', Auth::id())->sum('qty')]);
    }

    /** Full server cart for the logged-in user (cross-device hydration). */
    public function index(Request $request)
    {
        $items = CartItem::where('user_id', Auth::id())
            ->with(['product:id,name,image,price,sale_price', 'variant:id,product_id,value_1,value_2,price,sale_price'])
            ->get()
            ->map(function (CartItem $row) {
                $p = $row->product;
                $v = $row->variant;

                return [
                    'id' => $row->product_id,
                    'variantId' => $row->product_variant_id,
                    'variantValues' => $v
                        ? collect([$v->value_1, $v->value_2])->filter()->implode(', ')
                        : null,
                    'name' => $p?->name,
                    'price' => (float) ($v
                        ? ($v->sale_price ?? $v->price)
                        : ($p?->sale_price ?? $p?->price)),
                    'img' => $p?->image_url,
                    'qty' => $row->qty,
                ];
            });

        return response()->json(['ok' => true, 'items' => $items]);
    }

    public function clear(Request $request)
    {
        CartItem::where('user_id', Auth::id())->delete();

        return response()->json(['ok' => true]);
    }
}
