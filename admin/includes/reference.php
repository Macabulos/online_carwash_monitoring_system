<?php
function generateBookingReference($bookingID) {
    return 'REF-' . str_pad($bookingID, 7, '0', STR_PAD_LEFT);
}
?>
