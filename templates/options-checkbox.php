<?php
if($value) {
    $value = ' checked';
}
else {
    $value = '';
}
echo '<input type="checkbox" name="'.$name.'" id="'.$name.'"'.$value.'/>';