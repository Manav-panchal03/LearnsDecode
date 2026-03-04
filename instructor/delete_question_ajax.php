<?php
require '../config/config.php';
if(isset($_POST['id'])) {
    $id = $_POST['id'];
    // ક્વેરી: પ્રશ્ન અને તેના ઓપ્શન્સ ડિલીટ કરવા (જો Database માં Cascade Delete સેટ હોય તો એક જ ક્વેરી ચાલશે)
    mysqli_query($conn, "DELETE FROM quiz_options WHERE question_id = $id");
    mysqli_query($conn, "DELETE FROM quiz_questions WHERE id = $id");
    echo "success";
}
?>