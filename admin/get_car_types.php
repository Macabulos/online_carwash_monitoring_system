<?php
require_once '../connection/conn.php';

$service_id = $_GET['service_id'];
$car_types = mysqli_query($conn, "SELECT * FROM car_types");
$associations = mysqli_query($conn, "SELECT * FROM service_car_types WHERE ServiceID = $service_id");

// Create array of associated car types with their additional prices
$associated_types = [];
while ($row = mysqli_fetch_assoc($associations)) {
    $associated_types[$row['CarTypeID']] = $row['AdditionalPrice'];
}
?>

<div class="row">
    <?php while ($car_type = mysqli_fetch_assoc($car_types)): ?>
    <div class="col-md-6">
        <div class="car-type-card">
            <h5><?php echo $car_type['TypeName']; ?></h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="car_types[<?php echo $car_type['CarTypeID']; ?>][enabled]" 
                    id="carType<?php echo $car_type['CarTypeID']; ?>" 
                    <?php echo isset($associated_types[$car_type['CarTypeID']]) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="carType<?php echo $car_type['CarTypeID']; ?>">
                    Include this car type
                </label>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Additional Price:</span>
                <input type="number" class="form-control price-input" 
                    name="car_types[<?php echo $car_type['CarTypeID']; ?>][price]" 
                    step="0.01" min="0" 
                    value="<?php echo isset($associated_types[$car_type['CarTypeID']]) ? $associated_types[$car_type['CarTypeID']] : '0.00'; ?>">
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>