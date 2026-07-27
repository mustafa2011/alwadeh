<?php
$id=(int)($_GET['id']??0);
if($id<=0){
    die('Invalid item.');
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
        <i class="bi bi-box-seam"></i>
        Item Details
    </h3>
    <a href="?page=items" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i>
        Back
    </a>
</div>
<div id="alertContainer"></div>
<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered">
            <tbody id="itemDetails"></tbody>
        </table>
    </div>
</div>
<script>
const itemId=<?php echo $id;?>;
</script>
<!-- <script src="assets/js/item_view.js"></script> -->

<script>
    const itemId=<?php echo $id;?>;
</script>
<script src="assets/js/item_details.js"></script>
