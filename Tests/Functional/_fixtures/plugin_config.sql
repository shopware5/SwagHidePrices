SET @formId = (SELECT id FROM s_core_config_forms WHERE name LIKE "SwagHidePrices");

SET @elementPriceId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND name LIKE "show_prices");
SET @elementGroupId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND name LIKE "show_group");

INSERT INTO `s_core_config_values` (`id`, `element_id`, `shop_id`, `value`) VALUES
(109,	@elementPriceId,	1,	's:1:\"2\";'),
(110,	@elementPriceId,	2,	'i:1;'),
(111,	@elementGroupId,	1,	'a:2:{i:0;s:1:\"H\";i:1;s:2:\"EK\";}'),
(112,	@elementGroupId,	2,	'a:0:{}');
