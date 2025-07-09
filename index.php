<?php
$mysqli = new mysqli("localhost", "root", "", "midterm");

if (isset($_POST['save'])) {
    $product = $_POST['product_name'];
    $qty = $_POST['quantity'];
    $price = $_POST['price'];
    $delivery = $_POST['delivery'];
    $order_date = $_POST['order_date'];
    $total = ($qty * $price) + $delivery;

    if (!empty($_POST['edit_id'])) {
        
        $id = $_POST['edit_id'];
        $stmt = $mysqli->prepare("UPDATE orders SET product_name=?, quantity=?, price=?, delivery=?, total_price=?, order_date=? WHERE id=?");
        $stmt->bind_param("sidddsi", $product, $qty, $price, $delivery, $total, $order_date, $id);
        $stmt->execute();

        header("Location: index.php");
        exit;
    } else {
        $stmt = $mysqli->prepare("INSERT INTO orders (product_name, quantity, price, delivery, total_price, order_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siddds", $product, $qty, $price, $delivery, $total, $order_date);
        $stmt->execute();
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM orders WHERE id=$id");
    header("Location: index.php");
    exit;
}

$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $result_edit = $mysqli->query("SELECT * FROM orders WHERE id=$id");
    $edit_data = $result_edit->fetch_assoc();
}

$result = $mysqli->query("SELECT * FROM orders");

$total_products = $mysqli->query("SELECT COUNT(DISTINCT product_name) FROM orders")->fetch_row()[0];
$total_payment = $mysqli->query("SELECT IFNULL(SUM(total_price), 0) FROM orders")->fetch_row()[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Online Shopping</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <form method="POST" class="mb-4 shadow-sm p-3 bg-light rounded">
        <h4 class="text-center text-info">Online Shopping</h4>
        <input type="hidden" name="edit_id" value="<?= $edit_mode ? $edit_data['id'] : '' ?>">
        <label>Product Name:</label>
        <input type="text" name="product_name" placeholder="Product Name" class="form-control mb-2" required value="<?= $edit_mode ? htmlspecialchars($edit_data['product_name']) : '' ?>">

        <label>Quantity:</label>
        <input type="number" name="quantity" placeholder="Quantity" class="form-control mb-2" required value="<?= $edit_mode ? $edit_data['quantity'] : '' ?>">

        <label>Price:</label>
        <input type="number" name="price" placeholder="Price" step="0.01" class="form-control mb-2" required value="<?= $edit_mode ? $edit_data['price'] : '' ?>">

        <label>Delivery:</label>
        <input type="number" name="delivery" placeholder="Delivery" step="0.01" class="form-control mb-2" value="<?= $edit_mode ? $edit_data['delivery'] : '' ?>">

        <label>Order Date:</label>
        <input type="date" name="order_date" class="form-control mb-3" required value="<?= $edit_mode ? $edit_data['order_date'] : '' ?>">

        <button class="btn btn-<?= $edit_mode ? 'success' : 'danger' ?> w-100" name="save">
            <?= $edit_mode ? 'Update Order' : 'Save Order' ?>
        </button>
    </form>

    <div class="card mb-3 shadow-sm">
    <div class="card-header bg-success text-white text-center"><b>Order Records</b></div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped m-0">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Delivery</th>
                        <th>Total Price</th>
                        <th>Order Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= number_format($row['price'], 2) ?>$</td>
                            <td><?= number_format($row['delivery'], 2) ?>$</td>
                            <td><b><?= number_format($row['total_price'], 2) ?>$</b></td>
                            <td><?= $row['order_date'] ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this order?')">Delete</a>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body bg-light">
            <div class="row text-center">
                <div class="col-md-6 mb-2">
                    <b>Total Product:</b> 
                    <span class="badge bg-primary"><?= $total_products ?></span>
                </div>
                <div class="col-md-6">
                    <b>Payment:</b> 
                    <span class="badge bg-primary"><?= number_format($total_payment, 2) ?>$</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
