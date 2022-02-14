<?php if (isset($collection['shops']) && count($collection['shops'])) { ?>
<section class="section" >
    <div class="container">
        <div class="d-flex justify-content-between mb-7">
            <div class="section__title">
                <h5><?php echo $collection['collection_description']; ?></h5>
                <h2><?php echo ($collection['collection_name'] != '') ? $collection['collection_name'] : ''; ?></h2>
            </div>
        </div>
        <?php include('shop-layout-1-list.php'); ?>
    </div>
</section>
<?php } ?>