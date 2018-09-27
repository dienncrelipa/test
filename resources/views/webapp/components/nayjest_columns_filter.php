<?php
/**
 * Created by PhpStorm.
 * User: BinhPT
 * Date: 2/13/2017
 * Time: 4:58 PM
 */
$cfg = $filter->getConfig();
$filtersName = $grid->getConfig()->getName();
$name_filed = $cfg->getName() . '-' . $cfg->getOperator();
$filters = array_column(app('request')->all(), 'filters');
$option = '';
if($filters && isset($filters[0][$name_filed])) {
    $option = $filters[0][$name_filed];
}
?>

<select name="<?= $filtersName . '[filters][' . $name_filed .']' ?>" class="form-control input-sm" id="<?= 'filter-'.$name_filed ?>">
    <?php
        if($option != ''){
            echo "<option value='$option' selected='selected'></option>";
        }
    ?>
</select>