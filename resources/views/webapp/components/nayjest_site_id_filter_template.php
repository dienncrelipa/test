<?php
/**
 * Created by PhpStorm.
 * User: HungLV
 * Date: 06/29/2017
 */

$cfg = $filter->getConfig();
$filtersName = $grid->getConfig()->getName();
$name_filed = $cfg->getName() . '-' . $cfg->getOperator();
$filters = array_column(app('request')->all(), 'filters');
$options = '';
$options = \App\Models\Site::select(array('id', 'name'))->where('status', '=', 1)->get();

if ($filters && isset($filters[0][$name_filed])) {
    $selectedVal = $filters[0][$name_filed];
} else {
    $selectedVal = 0;
}

?>

<select name="<?= $filtersName . '[filters][' . $name_filed . ']' ?>" class="form-control input-sm" id="<?= 'filter-' . $name_filed ?>">
    <?php
        if ($options) {
            foreach ($options as $item) {
                $selected = ($item->id == $selectedVal ? 'selected' : '');
                echo "<option value='$item->id' $selected>$item->name</option>";
            }
        } else {
            echo "<option value='0' selected='selected'></option>";
        }
    ?>
</select>