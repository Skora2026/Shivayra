@extends('front.layouts.app')

@section('title')
    Shop
@endsection

@section('content')
    <div class="container py-lg-5 py-3">
        <div class="row">
            <div class="col-lg-3 d-lg-block d-none mb-4">
                <div class="filter-box">

                    <!-- Category Filter -->
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#categoryFilter"
                            aria-expanded="false">
                            Categories
                        </button>
                        <div class="collapse show" id="categoryFilter">
                            <div class="filter-content">
                                @foreach(\App\Models\Category::where('status', 'active')->with(['subCategories' => function($q) { $q->where('status', 'active'); }])->get() as $cat)
                                    <div class="category-filter-group mb-2">
                                        <label class="fw-bold" style="cursor: pointer;">
                                            <input type="checkbox" class="main-cat-filter" value="{{ $cat->slug }}"> {{ $cat->name }}
                                        </label>
                                        @if($cat->subCategories->isNotEmpty())
                                            <div class="subcat-filter-group ms-3">
                                                @foreach($cat->subCategories as $sub)
                                                    <label class="text-muted" style="font-size: 0.9em; cursor: pointer;">
                                                        <input type="checkbox" class="cat-filter" data-parent="{{ $cat->slug }}" value="{{ $sub->slug }}"> {{ $sub->name }}
                                                    </label><br>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">

                    <!-- Purity / Carat Filter -->
                    @if($filterData['purities']->isNotEmpty())
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#patternFilter"
                            aria-expanded="false">
                            Purity / Carat
                        </button>
                        <div class="collapse show" id="patternFilter">
                            <div class="filter-content">
                                @foreach($filterData['purities'] as $purity)
                                    <label><input type="checkbox" class="pattern-filter" value="{{ $purity }}"> {{ $purity }}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">
                    @endif

                    <!-- Occasion Filter -->
                    @if($filterData['occasions']->isNotEmpty())
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#occasionFilter"
                            aria-expanded="false">
                            Occasion
                        </button>
                        <div class="collapse show" id="occasionFilter">
                            <div class="filter-content">
                                @foreach($filterData['occasions'] as $occasion)
                                    <label><input type="checkbox" class="occasion-filter" value="{{ $occasion }}"> {{ $occasion }}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">
                    @endif

                    <!-- Metal / Material Filter -->
                    @if($filterData['metals']->isNotEmpty())
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#fabricFilter"
                            aria-expanded="false">
                            Metal / Material
                        </button>
                        <div class="collapse show" id="fabricFilter">
                            <div class="filter-content">
                                @foreach($filterData['metals'] as $metal)
                                    <label><input type="checkbox" class="fabric-filter" value="{{ $metal }}"> {{ $metal }}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">
                    @endif

                    <!-- Gemstone Filter -->
                    @if($filterData['gemstones']->isNotEmpty())
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#necklineFilter"
                            aria-expanded="false">
                            Gemstone
                        </button>
                        <div class="collapse show" id="necklineFilter">
                            <div class="filter-content">
                                @foreach($filterData['gemstones'] as $gemstone)
                                    <label><input type="checkbox" class="neckline-filter" value="{{ $gemstone }}"> {{ $gemstone }}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">
                    @endif

                    <!-- Size Filter -->
                    @if($filterData['sizes']->isNotEmpty())
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse" data-bs-target="#sizeFilter"
                            aria-expanded="false">
                            Size
                        </button>
                        <div class="collapse show" id="sizeFilter">
                            <div class="filter-content">
                                @foreach($filterData['sizes'] as $size)
                                    <label><input type="checkbox" class="size-filter" value="{{ $size }}"> {{ $size }}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">
                    @endif

                    <!-- Metal Color Filter -->
                    @if($filterData['colors']->isNotEmpty())
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse"
                            data-bs-target="#colorFilter" aria-expanded="false">
                            Metal Color
                        </button>
                        <div class="collapse show" id="colorFilter">
                            <div class="filter-content">
                                @foreach($filterData['colors'] as $color)
                                    <label><input type="checkbox" class="color-filter" value="{{ $color }}"> {{ $color }}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <hr class="my-1">
                    @endif

                    <!-- Price Filter -->
                    <div class="filter-section">
                        <button class="filter-btn" type="button" data-bs-toggle="collapse"
                            data-bs-target="#priceFilter" aria-expanded="false">
                            Price
                        </button>
                        <div class="collapse show" id="priceFilter">
                            <div class="filter-content">
                                <input type="range" min="100" max="{{ $maxPrice }}" value="{{ $maxPrice }}" id="priceRange"
                                    class="price-slider">
                                <p>Up to ₹ <span id="priceValue">{{ $maxPrice }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <button id="resetFiltersBtn" class="btn btn-danger btn-sm w-100 mt-3">Reset All Filters</button>

                </div>
            </div>

            {{-- filter for mobile --}}
            <div class="mobile-filter-bar d-lg-none mb-3">
                <button class="filter-chip active" data-target="mCategory">Categories</button>
                @if($filterData['purities']->isNotEmpty()) <button class="filter-chip" data-target="mPattern">Purity</button> @endif
                @if($filterData['occasions']->isNotEmpty()) <button class="filter-chip" data-target="mOccasion">Occasion</button> @endif
                @if($filterData['metals']->isNotEmpty()) <button class="filter-chip" data-target="mFabric">Metal</button> @endif
                @if($filterData['gemstones']->isNotEmpty()) <button class="filter-chip" data-target="mNeckline">Gemstone</button> @endif
                @if($filterData['sizes']->isNotEmpty()) <button class="filter-chip" data-target="mSize">Size</button> @endif
                @if($filterData['colors']->isNotEmpty()) <button class="filter-chip" data-target="mColor">Color</button> @endif
                <button class="filter-chip" data-target="mPrice">Price</button>
            </div>

            <div class="mobile-filter-content d-lg-none mb-3">
                <div id="mCategory" class="filter-panel active">
                    @foreach(\App\Models\Category::where('status', 'active')->with(['subCategories' => function($q) { $q->where('status', 'active'); }])->get() as $cat)
                        <div class="category-filter-group mb-2">
                            <label class="fw-bold" style="cursor: pointer;">
                                <input type="checkbox" class="main-cat-filter" value="{{ $cat->slug }}"> {{ $cat->name }}
                            </label>
                            @if($cat->subCategories->isNotEmpty())
                                <div class="subcat-filter-group ms-3">
                                    @foreach($cat->subCategories as $sub)
                                        <label class="text-muted" style="font-size: 0.9em; cursor: pointer;">
                                            <input type="checkbox" class="cat-filter" data-parent="{{ $cat->slug }}" value="{{ $sub->slug }}"> {{ $sub->name }}
                                        </label><br>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($filterData['purities']->isNotEmpty())
                <div id="mPattern" class="filter-panel">
                    @foreach($filterData['purities'] as $purity)
                        <label><input type="checkbox" class="pattern-filter" value="{{ $purity }}"> {{ $purity }}</label><br>
                    @endforeach
                </div>
                @endif
                @if($filterData['occasions']->isNotEmpty())
                <div id="mOccasion" class="filter-panel">
                    @foreach($filterData['occasions'] as $occasion)
                        <label><input type="checkbox" class="occasion-filter" value="{{ $occasion }}"> {{ $occasion }}</label><br>
                    @endforeach
                </div>
                @endif
                @if($filterData['metals']->isNotEmpty())
                <div id="mFabric" class="filter-panel">
                    @foreach($filterData['metals'] as $metal)
                        <label><input type="checkbox" class="fabric-filter" value="{{ $metal }}"> {{ $metal }}</label><br>
                    @endforeach
                </div>
                @endif
                @if($filterData['gemstones']->isNotEmpty())
                <div id="mNeckline" class="filter-panel">
                    @foreach($filterData['gemstones'] as $gemstone)
                        <label><input type="checkbox" class="neckline-filter" value="{{ $gemstone }}"> {{ $gemstone }}</label><br>
                    @endforeach
                </div>
                @endif
                @if($filterData['sizes']->isNotEmpty())
                <div id="mSize" class="filter-panel">
                    @foreach($filterData['sizes'] as $size)
                        <label><input type="checkbox" class="size-filter" value="{{ $size }}"> {{ $size }}</label><br>
                    @endforeach
                </div>
                @endif
                @if($filterData['colors']->isNotEmpty())
                <div id="mColor" class="filter-panel">
                    @foreach($filterData['colors'] as $color)
                        <label><input type="checkbox" class="color-filter" value="{{ $color }}"> {{ $color }}</label><br>
                    @endforeach
                </div>
                @endif
                <div id="mPrice" class="filter-panel">
                    <input type="range" min="100" max="{{ $maxPrice }}" value="{{ $maxPrice }}" id="mobilePriceRange"
                        class="price-slider">
                    <p>Up to ₹ <span id="mobilePriceValue">{{ $maxPrice }}</span></p>
                </div>
                <button id="mobileResetBtn" class="btn btn-danger btn-sm w-100 mt-2">Reset Filters</button>
            </div>

            {{-- Products Section --}}
            <div class="col-lg-9">
                <div class="row g-3" id="productRow">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <strong>Best Selling</strong>
                            <div class="d-flex align-items-center gap-3">
                                <select id="sortSelect" class="form-select form-select-sm" style="width:180px">
                                    <option value="">Sort By</option>
                                    <option value="low">Price : Low to High</option>
                                    <option value="high">Price : High to Low</option>
                                </select>
                                <div class="grid-switcher d-lg-block d-none">
                                    <button class="btn grid-btn active" data-grid="4">|||</button>
                                    <button class="btn grid-btn" data-grid="6">||</button>
                                    <button class="btn grid-btn" data-grid="12">|</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach($products as $product)
                    <div class="col-lg-4 col-md-6 col-6 product-grid" 
                          data-name="{{ strtolower($product->name) }}"
                          data-description="{{ strtolower($product->description) }}"
                          data-main-category="{{ $product->category ? $product->category->slug : '' }}"
                          data-category="{{ $product->subCategory ? $product->subCategory->slug : '' }}" 
                          data-size="{{ $product->size }}"
                          data-price="{{ (int)($product->sale_price ?? $product->price) }}" 
                          data-pattern="{{ $product->pattern }}" 
                          data-occasion="{{ $product->occasion }}" 
                          data-fabric="{{ $product->fabric }}"
                          data-color="{{ $product->color }}" 
                          data-neckline="{{ $product->neckline }}">
                        <div class="product-card">
                            <a href="{{ route('product_detail', $product->slug) }}">
                                <span class="product-badge hot"><svg class="icon"><use href="#i-sparkle"/></svg>Hot</span>
                                <div class="product-img">
                                    <img src="{{ $product->image_url }}">
                                </div>
                            </a>
                            <div class="product-body">
                                <h6>{{ $product->name }}</h6>
                                <div class="pp-price">
                                    <strong>₹{{ number_format($product->sale_price ?? $product->price, 0) }}</strong>
                                    @if($product->sale_price)
                                        <del>₹{{ number_format($product->price, 0) }}</del>
                                    @endif
                                </div>
                            </div>
                            <div class="pp-hover">
                                <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="pp-hover-btn add-to-wishlist" data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}" data-price="{{ $product->sale_price ?? $product->price }}"
                                    data-was="{{ $product->price }}" data-slug="{{ $product->slug }}"
                                    data-img="{{ $product->image_url }}">
                                    <i class="bi bi-heart"></i>
                                </button>
                                @php
                                    // Quick-add from the card: first in-stock variant;
                                    // all sold out -> fall back to the product page.
                                    $inStockVariants = $product->variants->filter(fn ($v) => $v->stock > 0);
                                    $quickVariant = $inStockVariants->first();
                                @endphp
                                @if($quickVariant)
                                    <button type="button" class="pp-hover-btn add-to-cart" data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-variant-id="{{ $quickVariant->id }}"
                                        data-variant-values="{{ collect([$quickVariant->value_1, $quickVariant->value_2])->filter()->implode(', ') }}"
                                        data-price="{{ $quickVariant->sale_price ?? $quickVariant->price }}"
                                        data-img="{{ $product->image_url }}">
                                        <i class="bi bi-bag-plus"></i>
                                    </button>
                                    @if($inStockVariants->count() > 1)
                                        {{-- Tiny variant chips: pick size/weight straight from the card --}}
                                        <div class="variant-chips" data-for="{{ $product->id }}">
                                            @foreach ($inStockVariants as $v)
                                                <button type="button" class="variant-chip add-to-cart"
                                                    data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                    data-variant-id="{{ $v->id }}"
                                                    data-variant-values="{{ collect([$v->value_1, $v->value_2])->filter()->implode(', ') }}"
                                                    data-price="{{ $v->sale_price ?? $v->price }}"
                                                    data-img="{{ $product->image_url }}">{{ $v->value_1 }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                        <i class="bi bi-bag-plus"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@endsection
