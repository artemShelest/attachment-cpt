<?php
if(!empty($infotip)) {
    echo '<p><label for="'.$name.'">'.$infotip.'</label></p>';
}
if(is_array($value)) {
    $value = join(', ', $value);
}
if(isset($readonly) && $readonly) {
    $readonly = ' readonly';
}
else {
    $readonly = '';
}
echo '<textarea name="'.$name.'" id="'.$name.'" class="large-text code"'.$readonly.'>'.esc_attr($value).'</textarea>';