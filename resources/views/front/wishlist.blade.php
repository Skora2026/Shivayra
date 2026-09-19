@extends('front.layouts.app')

@section('title')
    Wishlist
@endsection

@section('content')

    <section class="wishlist-section py-5">
        <div class="container">

            <div class="text-center mb-5">

                <h2 class="main-heading icon-inline">
                    <svg class="icon" style="color: var(--ruby);"><use href="#i-heart-fill"/></svg> My Wishlist
                </h2>

                <p class="creative-subline">
                    Your Favorites, All in One Place
                </p>

            </div>

            <div class="row g-4" id="wishlistItems">




            </div>
        </div>
    </section>

@endsection