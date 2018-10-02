<?php
/** @var Nayjest\Grids\Filter $filter */
/** @var Nayjest\Grids\SelectFilterConfig $cfg */
$cfg = $filter->getConfig();
$filtersName = $grid->getConfig()->getName();
$options = $cfg->getOptions();
$target_post_list = isset($options['target_post_list']) ? $options['target_post_list'] : array();
$site_list = isset($options['site_list']) ? $options['site_list'] : $options;
$name_filed = $cfg->getName() . '-' . $cfg->getOperator();
$filters = array_column(app('request')->all(), 'filters');
$selected = '';
if($filters && isset($filters[0][$name_filed])) {
    $selected = $filters[0][$name_filed];
}
?>

<select name="<?= $filtersName . '[filters][' . $name_filed .']' ?>" id="">
  <option value="">--//--</option>
  <?php foreach ($site_list as $value => $text) { ?>
  <option value="SITE-<?= $value ?>" <?php echo $selected == "SITE-$value" ? 'selected' : '' ?>><?= $text ?></option>
  <?php } ?>
  <?php foreach ($target_post_list as $post) { ?>
  <option value="POST-<?= $post->id ?>" <?php echo $selected == "POST-$post->id" ? 'selected' : '' ?>><?= $post->site_url . '?p=' .$post->target_post_id ?></option>
  <?php } ?>
</select>
