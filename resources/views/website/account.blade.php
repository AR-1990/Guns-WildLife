{{-- resources/views/website/account.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Your Guns & Wildlife Account | Order & Profile Dashboard')

@push('meta')
<meta name="description" content="Manage your orders, track history, update details, and access wishlist items securely through your Guns & Wildlife account.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>User</h3>
                        <h2>Account</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg wow fadeInRight" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="img"></figure>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">User</h2>
    </div>

    <!-- Begin: Account Section -->
    <section class="myAccount">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <ul class="nav nav-tabs tabNavStyle" id="bestSellerTab" role="tablist">
                        <li>
                            <div class="userNamePic">
                                <img src="{{ asset('assets/images/user.jpg') }}" alt="">
                                <div class="content">
                                    <h4>Hello</h4>
                                    <p>John Doe</p>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="dashboard-tab" data-toggle="tab" href="#dashboard" role="tab"
                                aria-controls="dashboard" aria-selected="false"><i class="fal fa-desktop-alt"></i> My Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="account-tab" data-toggle="tab" href="#account" role="tab" aria-controls="account"
                                aria-selected="false"><i class="fal fa-user"></i> My Account Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="orders-tab" data-toggle="tab" href="#orders" role="tab" aria-controls="orders"
                                aria-selected="true"><i class="fal fa-box"></i> My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="wishlist-tab" data-toggle="tab" href="#wishlist" role="tab"
                                aria-controls="wishlist" aria-selected="false"><i class="fal fa-heart"></i> My Wishlist</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab" aria-controls="address"
                                aria-selected="false"><i class="fal fa-book"></i> My Addresses</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-8">
                    <div class="tab-content">
                        {{-- Dashboard Tab --}}
                        <div role="tabpanel" class="tab-pane active" id="dashboard">
                            <div class="myDashboardTab">
                                <h2 class="title">My Dashboard</h2>
                                <div class="mt-3 mb-5">
                                    <h4>My Dashboard</h4>
                                    <p>From this dashboard you have the ability to view a snapshot of your recent account activity and
                                        update your account information. Select a link below to view or edit information.</p>
                                </div>
                                <h4>Recent Orders</h4>
                                <div class="noRecord">
                                    <p>No Record Found</p>
                                </div>
                            </div>
                        </div>

                        {{-- Account Info Tab --}}
                        <div role="tabpanel" class="tab-pane" id="account">
                            <div class="accounTab">
                                <h2 class="title">My Account Information</h2>
                                <form action="{{ route('account.update') }}" method="POST" class="row formStyle">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-6">
                                        <label>First Name <span>*</span></label>
                                        <input type="text" name="first_name" class="form-control" placeholder="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Last Name <span>*</span></label>
                                        <input type="text" name="last_name" class="form-control" placeholder="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Email <span>*</span></label>
                                        <input type="email" name="email" class="form-control" placeholder="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Mobile Number <span>*</span></label>
                                        <div class="CNum">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/british.jpg') }}" alt=""> +025</a>
                                                        <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097</a>
                                                    </div>
                                                </div>
                                                <input type="text" name="mobile" class="form-control" placeholder="50 123 4567">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="">Gender</label>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="male" name="gender" value="male" class="custom-control-input">
                                            <label class="custom-control-label" for="male">Male</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="female" name="gender" value="female" class="custom-control-input">
                                            <label class="custom-control-label" for="female">Female</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="subscribeCheckBox" name="subscribe">
                                            <label class="custom-control-label" for="subscribeCheckBox">I would like to receive emails and SMS
                                                regarding the promotions and special offers.</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="changePasswordCheckbox" name="change_password">
                                            <label class="custom-control-label" for="changePasswordCheckbox">Change Password?</label>
                                        </div>
                                        <div class="text-right"><button type="submit" class="themeBtn border-0">Save</button></div>
                                    </div>
                                    <div class="changePassword">
                                        <div class="col-md-12">
                                            <label>Old Password <span>*</span></label>
                                            <input type="password" name="old_password" class="form-control" placeholder="">
                                        </div>
                                        <div class="col-md-12">
                                            <label>New Password (At least 6 characters) <span>*</span></label>
                                            <input type="password" name="new_password" class="form-control" placeholder="">
                                        </div>
                                        <div class="col-md-12">
                                            <label>Confirm New Password <span>*</span></label>
                                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Orders Tab --}}
                        <div role="tabpanel" class="tab-pane" id="orders">
                            <div class="orderTab">
                                <h2 class="title">My Orders</h2>
                                <div class="table-responsive table-nowrap">
                                    <table class="table orderTable">
                                        <thead>
                                            <tr>
                                                <th>Order#</th>
                                                <th>Date</th>
                                                <th>Order Total</th>
                                                <th>Order Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < 6; $i++)
                                            <tr>
                                                <td><p><span>Order#</span>100000568</p></td>
                                                <td><p><span>Date</span>9/03/2020</p></td>
                                                <td><p><span>Order Total</span>$ 150.00</p></td>
                                                <td><p><span>Order Status</span>Delivered</p></td>
                                                <td>
                                                    <a href="#" class="btnStyle">Track Order</a>
                                                    <a href="javascript:void(0)" class="btnStyle" data-toggle="modal"
                                                        data-target="#OrderDetailModal">View Order</a>
                                                    <a href="#" class="btnStyle">Reorder</a>
                                                </td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Wishlist Tab --}}
                        <div role="tabpanel" class="tab-pane" id="wishlist">
                            <h2 class="title">My Wishlist</h2>
                            <div class="table-responsive table-nowrap">
                                <table class="table orderTable wishlistTable">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 0; $i < 4; $i++)
                                        <tr>
                                            <td>
                                                <div class="d-flex">
                                                    <img src="{{ asset('assets/images/pro-4.jpg') }}" alt="" class="img-fluid">
                                                    <p>Original Organic Honey</p>
                                                </div>
                                            </td>
                                            <td><p>$10.00</p></td>
                                            <td>
                                                <a href="#" class="btnStyle">Add to cart</a>
                                                <a href="#" class="btnStyle">Remove</a>
                                            </td>
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Address Tab --}}
                        <div role="tabpanel" class="tab-pane" id="address">
                            <h2 class="title">My Address <a href="javascript:void(0)" class="btnStyle" data-toggle="modal"
                                    data-target="#addNewsAddressModal"><i class="fa fa-plus-circle"></i> Add New Address</a></h2>
                            <div class="row">
                                @php
                                    $addresses = [
                                        ['tag' => 'Home', 'name' => 'John Doe', 'street' => '1705, Citadel Tower, Business Bay, Dubai', 'city' => 'Dubai', 'country' => 'United Arab Emirates', 'phone' => '+015 333 4567'],
                                        ['tag' => 'Work', 'name' => 'John Doe', 'street' => '1705, Citadel Tower, Business Bay, Dubai', 'city' => 'Dubai', 'country' => 'United Arab Emirates', 'phone' => '+015 333 4567'],
                                        ['tag' => 'Other', 'name' => 'John Doe', 'street' => '1705, Citadel Tower, Business Bay, Dubai', 'city' => 'Dubai', 'country' => 'United Arab Emirates', 'phone' => '+015 333 4567'],
                                    ];
                                @endphp
                                @foreach ($addresses as $address)
                                <div class="col-lg-6">
                                    <div class="adressCard">
                                        <span class="tag">{{ $address['tag'] }}</span>
                                        <address>
                                            <span>{{ $address['name'] }}</span>
                                            <span>{{ $address['street'] }}</span>
                                            <span>{{ $address['city'] }}, </span>
                                            <span>{{ $address['country'] }}</span>
                                            <span>{{ $address['phone'] }}</span>
                                        </address>
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#editAddressModal" class="edit"><i
                                                class="fa fa-pencil"></i></a>
                                        <a href="#" class="remove"><i class="fa fa-trash"></i></a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END: Account Section -->

    {{-- Add New Address Modal --}}
    <div class="modal fade accountAccesSec" id="addNewsAddressModal" tabindex="-1" aria-labelledby="addNewsAddressModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title m-0">Add New Address</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('address.store') }}" method="POST" class="row formStyle">
                        @csrf
                        <div class="col-md-6">
                            <label>First Name <span>*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Last Name <span>*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Mobile Number <span>*</span></label>
                            <div class="CNum">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/british.jpg') }}" alt=""> +025</a>
                                            <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097</a>
                                        </div>
                                    </div>
                                    <input type="text" name="mobile" class="form-control" placeholder="50 123 4567">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Title <span>*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Street Address <span>*</span></label>
                            <input type="text" name="street" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>City <span>*</span></label>
                            <select class="form-control" name="city">
                                <option value=""> - Select City -</option>
                                <option value="Makkah">Makkah</option>
                                <option value="Madina">Madina</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Company Name <span>*</span></label>
                            <input type="text" name="company" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Zip Code <span>*</span></label>
                            <input type="text" name="zip" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Country <span>*</span></label>
                            <select class="form-control" name="country">
                                <option value="UAE">UAE</option>
                                <option value="Pakistan">Pakistan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>State <span>*</span></label>
                            <input type="text" name="state" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-12">
                            <div class="custom-control custom-checkbox ml-3">
                                <input type="checkbox" class="custom-control-input" id="saveAddressBook" name="save_in_book">
                                <label class="custom-control-label" for="saveAddressBook">Save in address book</label>
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn-border" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btnStyle">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Shipping Address Modal --}}
    <div class="modal fade accountAccesSec" id="shippingAddressModal" tabindex="-1" aria-labelledby="shippingAddressModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title m-0">Shipping Address</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="#" method="POST" class="row formStyle">
                        @csrf
                        <div class="col-md-6">
                            <label>First Name <span>*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Last Name <span>*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Mobile Number <span>*</span></label>
                            <div class="CNum">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/british.jpg') }}" alt=""> +025</a>
                                            <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097</a>
                                        </div>
                                    </div>
                                    <input type="text" name="mobile" class="form-control" placeholder="50 123 4567">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Title <span>*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Street Address <span>*</span></label>
                            <input type="text" name="street" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>City <span>*</span></label>
                            <select class="form-control" name="city">
                                <option value=""> - Select City -</option>
                                <option value="Makkah">Makkah</option>
                                <option value="Madina">Madina</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Company Name <span>*</span></label>
                            <input type="text" name="company" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Zip Code <span>*</span></label>
                            <input type="text" name="zip" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Country <span>*</span></label>
                            <select class="form-control" name="country">
                                <option value="UAE">UAE</option>
                                <option value="Pakistan">Pakistan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>State <span>*</span></label>
                            <input type="text" name="state" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-12">
                            <div class="custom-control custom-checkbox ml-3">
                                <input type="checkbox" class="custom-control-input" id="saveShippingBook" name="save_in_book">
                                <label class="custom-control-label" for="saveShippingBook">Save in address book</label>
                            </div>
                            <div class="text-right border-top py-4 mt-4">
                                <button type="button" class="btn-border" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btnStyle">Ship Here</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Address Modal --}}
    <div class="modal fade accountAccesSec" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title m-0">Edit Address</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('address.update') }}" method="POST" class="row formStyle">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <label>First Name <span>*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Last Name <span>*</span></label>
                            <input type="text" name="last_name" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Mobile Number <span>*</span></label>
                            <div class="CNum">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/british.jpg') }}" alt=""> +025</a>
                                            <a class="dropdown-item" href="#"><img src="{{ asset('assets/images/uae.jpg') }}" alt=""> +097</a>
                                        </div>
                                    </div>
                                    <input type="text" name="mobile" class="form-control" placeholder="50 123 4567">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Title <span>*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Street Address <span>*</span></label>
                            <input type="text" name="street" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>City <span>*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Company Name <span>*</span></label>
                            <input type="text" name="company" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Zip Code <span>*</span></label>
                            <input type="text" name="zip" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label>Country <span>*</span></label>
                            <select class="form-control" name="country">
                                <option value="UAE">UAE</option>
                                <option value="Pakistan">Pakistan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>State <span>*</span></label>
                            <input type="text" name="state" class="form-control" placeholder="">
                        </div>
                        <div class="col-md-12">
                            <div class="custom-control custom-checkbox ml-3">
                                <input type="checkbox" class="custom-control-input" id="editSaveBook" name="save_in_book">
                                <label class="custom-control-label" for="editSaveBook">Save in address book</label>
                            </div>
                            <div class="text-right"><button type="submit" class="btnStyle">Save</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Detail Modal --}}
    <div class="modal fade accountAccesSec" id="OrderDetailModal" tabindex="-1" aria-labelledby="OrderDetailModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="m-0">My Order Details</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="orderTab">
                        <div class="alert alert-success" role="alert">
                            <span>Order# 10000568</span>
                            <span>Total: $150.00</span>
                            <span>Placed on June 25, 2020 15:48</span>
                            <span>Payment Method: Cash On Delivery</span>
                            <span>Ship to: John Doe</span>
                        </div>
                        <div class="table-responsive table-nowrap">
                            <table class="table orderTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 0; $i < 6; $i++)
                                    <tr>
                                        <td>
                                            <p><img src="{{ asset('assets/images/cart-item1.jpg') }}" alt="">Organic Honey</p>
                                        </td>
                                        <td><p>$10.00</p></td>
                                        <td><p>1</p></td>
                                        <td><p>$10.00</p></td>
                                        <td>
                                            <a href="#" class="btnStyle">Reorder</a>
                                            <a href="" class="btnStyle">Add Review</a>
                                        </td>
                                    </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection