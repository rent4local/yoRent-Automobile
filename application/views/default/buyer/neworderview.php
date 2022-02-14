<?php
$navData = [];
$this->includeTemplate('_partial/dashboardNavigation.php', $navData);
?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header">
            <div class="row">
                <div class="col">
                    <h2 class="content-header-title no-print">
                        Order Details </h2>
                </div>
                <div class="col-auto">
                    <div class="no-print">
                        <a class="btn btn-outline-brand btn-sm no-print" href="javascript:void(0)">
                            Cancel order
                        </a>
                        <a class="btn btn-outline-brand btn-sm no-print" href="javascript:void(0)">
                            Print
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <div class="order-number">
                            <small class="sm-txt">Order #</small>
                            <span class="numbers"> O1625736604-S0003 <span class="notice">29 Day(s) Remaining To End
                                    Rental</span> </span>
                        </div>
                    </h5>
                    <div class="btn-group orders-actions">
                        <a href="" class="btn btn-brand btn-sm">Buy Again</a>
                        <a href="" class="btn btn-outline-brand btn-sm">Invoice</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="table-wrap">
                                <table class="table table-orders">
                                    <thead>
                                        <tr class="">
                                            <th> Items Summary </th>
                                            <th> Item Price </th>
                                            <th> Total Price </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="item">
                                                    <figure class="item__pic">
                                                        <a
                                                            href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                            <img src="/yorent-v2.1/image/product/101/SMALL/275/0/1"
                                                                title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                alt="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)" />
                                                        </a>
                                                    </figure>
                                                    <div class="item__description">
                                                        <div class="item__title">
                                                            <a title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                                Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power
                                                                Adapter)<br> </a>
                                                        </div>


                                                        <div class="item__options">
                                                            QTY: 02 | Color: Black | Storage : 128GB </div>
                                                        <div class="item__sold_by">
                                                            Sold By: Kanwar's Shop </div>
                                                        <div class="item__date-range">
                                                            <small>
                                                                <i class="icn">
                                                                    <svg width="16px" height="16px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                        </use>
                                                                    </svg></i>
                                                                From: Jul 09, 2021 | To: Sep 06, 2021 </small>
                                                        </div>

                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                $XXX.XX </td>
                                            <td>
                                                $XXX.XX
                                            </td>

                                        </tr>
                                        <tr class="row-addons">
                                            <td colspan="3">
                                                <div class="addons">
                                                    <button class="addons_trigger collapsed" type="button"
                                                        data-toggle="collapse" data-target="#collapseExample"
                                                        aria-expanded="true" aria-controls="collapseExample">
                                                        <span class="txt">Addons And Details<span class="count">
                                                                2</span></span>
                                                        <i class="icn"></i>
                                                    </button>
                                                    <div class="collapse" id="collapseExample">
                                                        <ul class="addons-list">
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> Lorem, ipsum dolor </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="item">
                                                    <figure class="item__pic">
                                                        <a
                                                            href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                            <img src="/yorent-v2.1/image/product/101/SMALL/275/0/1"
                                                                title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                alt="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)" />
                                                        </a>
                                                    </figure>
                                                    <div class="item__description">
                                                        <div class="item__title">
                                                            <a title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                                Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power
                                                                Adapter)<br> </a>
                                                        </div>


                                                        <div class="item__options">
                                                            QTY: 02 | Color: Black | Storage : 128GB </div>
                                                        <div class="item__sold_by">
                                                            Sold By: Kanwar's Shop </div>
                                                        <div class="item__date-range">
                                                            <small>
                                                                <i class="icn">
                                                                    <svg width="16px" height="16px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                        </use>
                                                                    </svg></i>
                                                                From: Jul 09, 2021 | To: Sep 06, 2021 </small>
                                                        </div>

                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                $XXX.XX </td>
                                            <td>
                                                $XXX.XX
                                            </td>

                                        </tr>
                                        <tr class="row-addons">
                                            <td colspan="3">
                                                <div class="addons">
                                                    <button class="addons_trigger collapsed" type="button"
                                                        data-toggle="collapse" data-target="#collapseExample"
                                                        aria-expanded="true" aria-controls="collapseExample">
                                                        <span class="txt">Addons And Details<span class="count">
                                                                2</span></span>
                                                        <i class="icn"></i>
                                                    </button>
                                                    <div class="collapse" id="collapseExample">
                                                        <ul class="addons-list">
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> Lorem, ipsum dolor </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="item">
                                                    <figure class="item__pic">
                                                        <a
                                                            href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                            <img src="/yorent-v2.1/image/product/101/SMALL/275/0/1"
                                                                title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                alt="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)" />
                                                        </a>
                                                    </figure>
                                                    <div class="item__description">
                                                        <div class="item__title">
                                                            <a title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                                Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power
                                                                Adapter)<br> </a>
                                                        </div>


                                                        <div class="item__options">
                                                            QTY: 02 | Color: Black | Storage : 128GB </div>
                                                        <div class="item__sold_by">
                                                            Sold By: Kanwar's Shop </div>
                                                        <div class="item__date-range">
                                                            <small>
                                                                <i class="icn">
                                                                    <svg width="16px" height="16px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                        </use>
                                                                    </svg></i>
                                                                From: Jul 09, 2021 | To: Sep 06, 2021 </small>
                                                        </div>

                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                $XXX.XX </td>
                                            <td>
                                                $XXX.XX
                                            </td>

                                        </tr>
                                        <tr class="row-addons">
                                            <td colspan="3">
                                                <div class="addons">
                                                    <button class="addons_trigger collapsed" type="button"
                                                        data-toggle="collapse" data-target="#collapseExample"
                                                        aria-expanded="true" aria-controls="collapseExample">
                                                        <span class="txt">Addons And Details<span class="count">
                                                                2</span></span>
                                                        <i class="icn"></i>
                                                    </button>
                                                    <div class="collapse" id="collapseExample">
                                                        <ul class="addons-list">
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> Lorem, ipsum dolor </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="item">
                                                    <figure class="item__pic">
                                                        <a
                                                            href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                            <img src="/yorent-v2.1/image/product/101/SMALL/275/0/1"
                                                                title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                alt="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)" />
                                                        </a>
                                                    </figure>
                                                    <div class="item__description">
                                                        <div class="item__title">
                                                            <a title="Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power Adapter)"
                                                                href="/yorent-v2.1/apple-iphone-xr-black-128-gb-includes-earpods-power-adapter-275">
                                                                Apple iPhone XR (Black, 128 GB) (Includes EarPods, Power
                                                                Adapter)<br> </a>
                                                        </div>


                                                        <div class="item__options">
                                                            QTY: 02 | Color: Black | Storage : 128GB </div>
                                                        <div class="item__sold_by">
                                                            Sold By: Kanwar's Shop </div>
                                                        <div class="item__date-range">
                                                            <small>
                                                                <i class="icn">
                                                                    <svg width="16px" height="16px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                        </use>
                                                                    </svg></i>
                                                                From: Jul 09, 2021 | To: Sep 06, 2021 </small>
                                                        </div>

                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                $XXX.XX </td>
                                            <td>
                                                $XXX.XX
                                            </td>

                                        </tr>
                                        <tr class="row-addons">
                                            <td colspan="3">
                                                <div class="addons">
                                                    <button class="addons_trigger collapsed" type="button"
                                                        data-toggle="collapse" data-target="#collapseExample"
                                                        aria-expanded="true" aria-controls="collapseExample">
                                                        <span class="txt">Addons And Details<span class="count">
                                                                2</span></span>
                                                        <i class="icn"></i>
                                                    </button>
                                                    <div class="collapse" id="collapseExample">
                                                        <ul class="addons-list">
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> sit ameo explicabo
                                                                    temporibus ad sapiente Logo Design</div>
                                                            </li>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="/yorent-v2.1/image/addon-product/193/THUMB/0/1"
                                                                        title="Logo Design" alt="Logo Design">
                                                                </div>

                                                                <div class="addons-name"> Lorem, ipsum dolor </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="timelines-wrap">
                                <h5 class="card-title">Order Timeline</h5>
                                <ul class="timeline">
                                    <li class="enable in-process">
                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">31-03-2021 07:03 PM </time>
                                                <span class="order-status"> <em class="dot"></em> In progress
                                                </span>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim
                                                    eligendi
                                                    optio dolore natus, corporis autem temporibus sed deserunt
                                                    necessitatibus provident, voluptatibus nisi explicabo illum
                                                    assumenda
                                                    laborum nulla totam quas mollitia.</p>
                                            </div>
                                        </div>

                                    </li>
                                    <li class="enable ready-for-shipping">

                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">31-03-2021 07:03 PM </time>
                                                <span class="order-status"> <em class="dot"></em> Ready for Shipping
                                                </span>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim
                                                    eligendi
                                                    optio dolore natus, corporis autem temporibus sed deserunt
                                                    necessitatibus provident, voluptatibus nisi explicabo illum
                                                    assumenda
                                                    laborum nulla totam quas mollitia.</p>
                                            </div>
                                        </div>
                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">25-03-2021 02:08 PM </time>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim
                                                    eligendi</p>
                                            </div>
                                        </div>
                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">08-03-2021 01:08 PM </time>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Lorem ipsum dolor s it. Enim
                                                    eligendi</p>
                                            </div>
                                        </div>
                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">05-03-2021 02:08 PM </time>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elsectetur
                                                    adipisicing elit. Enim
                                                    eligendi</p>
                                            </div>
                                        </div>

                                    </li>
                                    <li class="enable shipped">

                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">31-03-2021 07:03 PM </time>
                                                <span class="order-status"> <em class="dot"></em> Shipped
                                                </span>
                                            </div>
                                            <div class="timeline_data_body">
                                                <h6>Tracking Number</h6>
                                                <div class="clipboard mb-4">

                                                    <p class="clipboard_url">ch_1Ib3v5L1bMNoOfFvOvCKmzRi

                                                    </p>
                                                    <a class="clipboard_btn" onclick="copyContent()"
                                                        href="javascript:void(0);"><i class="far fa-copy"></i></a>
                                                </div>

                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim
                                                    eligendi
                                                    optio dolore natus, corporis autem temporibus sed deserunt
                                                    necessitatibus provident, voluptatibus nisi explicabo illum
                                                    assumenda
                                                    laborum nulla totam quas mollitia.</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="enable currently delivered">
                                        <div class="timeline_data">
                                            <div class="timeline_data_head">
                                                <time class="timeline_date">31-03-2021 07:03 PM </time>
                                                <span class="order-status"> <em class="dot"></em> Delivered
                                                </span>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim
                                                    eligendi
                                                    optio dolore natus, corporis autem temporibus sed deserunt
                                                    necessitatibus provident, voluptatibus nisi explicabo illum
                                                    assumenda
                                                    laborum nulla totam quas mollitia.</p>
                                            </div>
                                        </div>


                                    </li>


                                    <li class="disabled shipped">
                                        <div class="timeline_data">
                                            <div class="timeline_data_head">

                                                <span class="order-status"> <em class="dot"></em> Ready for
                                                    pickup </span>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Expected by 16 August 2022</p>
                                            </div>
                                        </div>



                                    </li>
                                    <li class="disabled delivered">

                                        <div class="timeline_data">
                                            <div class="timeline_data_head">

                                                <span class="order-status"> <em class="dot"></em> Delivered </span>
                                            </div>
                                            <div class="timeline_data_body">
                                                <p>Expected by 28 August 2022</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="ml-xl-2">
                                <div class="order-block">
                                    <h4>Order Summary</h4>
                                    <div class="cart-summary">
                                        <ul class="">

                                            <li>
                                                <span class="label">Order Created</span>
                                                <span class="value">08/07/2021</span>
                                            </li>

                                            <li>
                                                <span class="label">Shipping Price</span>
                                                <span class="value">$00.00</span>
                                            </li>
                                            <li>
                                                <span class="label"><a class="dotted" href="">Security Amount</a>
                                                </span>
                                                <span class="value">$XX XX</span>
                                            </li>
                                            <li>
                                                <span class="label"> <a class="dotted" href=""> Taxes
                                                        <em class="count">5</em></a></span>
                                                <span class="value">$10.00</span>
                                            </li>
                                            <li>
                                                <span class="label">Payment Fee</span>
                                                <span class="value">$1,250.00</span>
                                            </li>
                                            <li class="discounted">
                                                <span class="label">Reward Point Discount </span>
                                                <span class="value">XXXX</span>
                                            </li>
                                            <li>
                                                <span class="label">Sub Total</span>
                                                <span class="value">$1,250.00</span>
                                            </li>
                                            <li class="highlighted">
                                                <span class="label">Total</span>
                                                <span class="value">$1,250.00</span>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                                <div class="total-savings">
                                    <img class="total-savings-img"
                                        src="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/savings.svg" alt="">
                                    <p>Your total savings amount on this order</p>
                                    <span class="amount">$100</span>

                                </div>
                                <div class="order-block">
                                    <h4>Order Details </h4>
                                    <h5>Shipping Address</h5>
                                    <div class="address-info">
                                        <p>Tribe Store 2 ,</p>
                                        <p>Unit No. A-712, Tower A, 7th Floor,
                                            Bestech Business Towers, Sector-66,</p>
                                        <p>
                                            Mohali, Punjab
                                            160066,
                                        </p>
                                        <p>India,</p>
                                        <p class="c-info">
                                            <strong>
                                                <i class="icn">
                                                    <svg width="16px" height="16px" class="svg">
                                                        <use
                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                        </use>
                                                    </svg></i>
                                                +91
                                                08559008860
                                            </strong>
                                        </p>
                                        <p class="c-info">
                                            <strong>
                                                <i class="icn">
                                                    <svg width="16px" height="16px" class="svg">
                                                        <use
                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                        </use>
                                                    </svg></i>
                                                5-4-2021
                                            </strong>
                                        </p>
                                        <p class="c-info">
                                            <strong>
                                                <i class="icn">
                                                    <svg width="16px" height="16px" class="svg">
                                                        <use
                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                        </use>
                                                    </svg></i>
                                                09:00 - 18:00
                                            </strong>
                                        </p>
                                    </div>
                                    <hr class="dotted">
                                    <h5>Payment Method :</h5>
                                    <div class="payment-mode">
                                        <div class="cc-payment">
                                            <span class="cc-num">Card </span>
                                        </div>
                                        <div class="txt-id">
                                            <p>
                                                <strong>Transaction number</strong>
                                                <br>
                                                ch_1Ib3v5L1bMNoOfFvOvCKmzRi
                                            </p>

                                        </div>
                                    </div>

                                </div>
                                <div class="order-block">
                                    <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                        data-target="#order-block2" aria-expanded="false" aria-controls="order-block2">
                                        Billing Address: <i class="dropdown-toggle-custom-arrow"></i></h4>
                                    <div class="collapse" id="order-block2">
                                        <div class="order-block-data">
                                            <div class="address-info">
                                                <p>John Doe,</p>
                                                <p>Beach Drive,</p>
                                                <p>Mumbai, Maharashtra 78956, </p>
                                                <p>India,</p>
                                                <p class="c-info">
                                                    <strong>
                                                        <i class="icn">
                                                            <svg width="16px" height="16px" class="svg">
                                                                <use
                                                                    xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                </use>
                                                            </svg></i>
                                                        +91
                                                        4578965987
                                                    </strong>
                                                </p>


                                            </div>
                                        </div>

                                    </div>

                                </div>
                                <div class="order-block">
                                    <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                        data-target="#order-block3" aria-expanded="false" aria-controls="order-block3">
                                        Pickup Address:
                                        <i class="dropdown-toggle-custom-arrow"></i>
                                    </h4>
                                    <div class="collapse" id="order-block3">
                                        <div class="order-block-data">
                                            <div class="address-info">
                                                <p>Tribe Store 2 ,</p>
                                                <p>Unit No. A-712, Tower A, 7th Floor,
                                                    Bestech Business Towers, Sector-66,</p>
                                                <p>

                                                    Mohali, Punjab
                                                    160066,
                                                </p>
                                                <p>India,</p>
                                                <p class="c-info">
                                                    <strong>
                                                        <i class="icn">
                                                            <svg width="16px" height="16px" class="svg">
                                                                <use
                                                                    xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                </use>
                                                            </svg></i>
                                                        +91
                                                        08559008860
                                                    </strong>
                                                </p>
                                                <p class="c-info">
                                                    <strong>
                                                        <i class="icn">
                                                            <svg width="16px" height="16px" class="svg">
                                                                <use
                                                                    xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                </use>
                                                            </svg></i>
                                                        5-4-2021
                                                    </strong>
                                                </p>
                                                <p class="c-info">
                                                    <strong>
                                                        <i class="icn">
                                                            <svg width="16px" height="16px" class="svg">
                                                                <use
                                                                    xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#test">
                                                                </use>
                                                            </svg></i>
                                                        09:00 - 18:00
                                                    </strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-block">
                                    <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                        data-target="#order-block6" aria-expanded="false" aria-controls="order-block6">
                                        Verification Data <i class="dropdown-toggle-custom-arrow"></i></h4>
                                    <div class="collapse" id="order-block6">
                                        <div class="order-block-data">
                                            <div class="list-specification">
                                                <ul class="">
                                                    <li>
                                                        <span class="label">inactive </span>
                                                        <span class="value">XXX XXX XXX</span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Single entry </span>
                                                        <span class="value">XXX xxx </span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Driving License </span>
                                                        <span class="value">XXX xxx XXX xxx XXX xxx </span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Pan Card </span>
                                                        <span class="value">XXX xxx XXX xxx </span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Aadhar123 </span>
                                                        <span class="value">XXX xxx XXX xxx XXX xxx </span>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                                <div class="order-block">
                                    <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                        data-target="#order-block6" aria-expanded="false" aria-controls="order-block6">
                                        Rental Agreement <i class="dropdown-toggle-custom-arrow"></i></h4>
                                    <div class="collapse" id="order-block6">

                                        <div class="order-block-data">

                                            <div class="list-specification">
                                                <ul class="">
                                                    <li>
                                                        <span class="label">Shop Name </span>
                                                        <span class="value">Kanwar's Shop</span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Shop Agreement </span>
                                                        <span class="value"><a href="#"> Koala.jpg</a> </span>
                                                    </li>
                                                    <li>


                                                    </li>
                                                    <li>


                                                    </li>


                                                </ul>

                                                <h5>Signature </h5>
                                                <img class="attached-img"
                                                    src="http://localhost/yorent-v2.1/image/signature/87/0/ORIGINAL/2916/1"
                                                    alt="">
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</main>