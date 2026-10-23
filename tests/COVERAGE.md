# Coverage checkpoint

Incomplete. Native PHP 8.2.30 / Xdebug 3.5.3, one main-process graph. Source baseline: `892b6dae8282b159e580e3c1a6076a39d9902f84`. Child graphs from direct-access, missing-PHAR, fallback-loader, and each EE version remain separate; no incompatible graphs or runtimes are merged.

All shipped sources are listed below, including unloaded archive files. `unknown` means instrumentation was absent and does not mean 100%. The line interval is inferred from Xdebug opcode metadata; raw per-file lines and exact uncovered exits are retained in the ignored report. Xdebug basic-block hits and outgoing exits are reported separately; basic-block 100% does not prove all conditional/loop outcomes. Synthetic terminal-exit edges are not silently discarded. Path coverage 100% is not required.

Main raw: `/Users/tomjaeger/Sites/add-ons/low_random/tests/reports/review-fixed-raw-82.91017.json`. Summary: `tests/reports/review-fixed-branches.json`. Skill Clover method report: `tests/reports/review-fixed-methods.json`; Clover has no real branch metrics, so its branch percentages are not proof.

| Source | Function/method | Lines | Basic blocks | Outgoing exits | Uncovered executable lines |
|---|---|---|---|---|---|
| `low_random/low_random.phar!/Views/license.php` | `{main}` | 14/14 | 0/6 | 0/8 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE.php` | `Low\Random\FluxCapacitor\Bridge\EE->__construct` | 0/2 | 1/1 | 0/1 | 44,45 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE.php` | `ee` | 0/1 | 0/1 | 0/0 | 20 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE.php` | `{main}` | 12/30 | 0/16 | 0/23 | 18,20,34,40,44,45,48,52,53,56,60,61,64,68,69,72,76,77 |
| `low_random/low_random.phar!/FluxCapacitor/FluxCapacitor.php` | `Low\Random\FluxCapacitor\FluxCapacitor->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/FluxCapacitor.php` | `{main}` | 5/5 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->get` | 2/2 | 4/4 | 4/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->delete` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->set` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->createKey` | 5/5 | 5/5 | 5/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->put` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `{main}` | 18/18 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->clear` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->has` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/StaticCache.php` | `Low\Random\FluxCapacitor\Conduit\StaticCache->all` | 1/1 | 1/1 | 0/0 | none |
| `low_random/pi.low_random.php` | `Low_random->filter` | 7/7 | 9/9 | 11/14 | none |
| `low_random/pi.low_random.php` | `Low_random->item` | 2/2 | 1/1 | 0/0 | none |
| `low_random/pi.low_random.php` | `{main}` | 45/46 | 0/3 | 0/4 | 4 |
| `low_random/pi.low_random.php` | `Low_random->letter` | 1/1 | 1/1 | 0/0 | none |
| `low_random/pi.low_random.php` | `Low_random->_random_item_from_set` | 2/2 | 4/4 | 4/4 | none |
| `low_random/pi.low_random.php` | `Low_random->items` | 6/6 | 3/3 | 3/3 | none |
| `low_random/pi.low_random.php` | `Low_random->file` | 18/18 | 15/15 | 20/22 | none |
| `low_random/pi.low_random.php` | `Low_random->number` | 1/1 | 1/1 | 0/0 | none |
| `low_random/pi.low_random.php` | `Low_random->_range` | 4/4 | 1/1 | 0/0 | none |
| `low_random/addon.setup.php` | `{main}` | 9/9 | 0/1 | 0/0 | none |
| `low_random/autoload.php` | `{main}` | 27/32 | 0/1 | 0/1 | 57,63,66,82,90 |
| `low_random/autoload.php` | `{closure:/Users/tomjaeger/Sites/add-ons/low_random/low_random/autoload.php:19-92}` | 25/30 | 24/31 | 24/44 | 57,63,66,82,90 |
| `low_random/views/license.php` | `{main}` | 14/14 | 0/6 | 0/8 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mod.php` | `Low\Random\FluxCapacitor\Base\Mod->getParameter` | 6/6 | 5/5 | 5/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mod.php` | `{main}` | 10/10 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mod.php` | `Low\Random\FluxCapacitor\Base\Mod->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Pi.php` | `Low\Random\FluxCapacitor\Base\Pi->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Pi.php` | `{main}` | 5/10 | 0/1 | 0/1 | 31,32,33,34,36 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Pi.php` | `Low\Random\FluxCapacitor\Base\Pi->info` | 1/6 | 1/1 | 0/1 | 31,32,33,34,36 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->xss_clean` | 0/1 | 0/1 | 0/0 | 365 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->flashData` | 0/14 | 0/13 | 0/19 | 295,298,299,300,301,303,307,308,311,314,316,317,322,323 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->assign_parameters` | 0/1 | 0/1 | 0/0 | 529 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getBaseURL` | 0/5 | 0/5 | 0/6 | 47,48,49,50,53 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->setConfig` | 15/16 | 6/7 | 7/10 | 231 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->removeEEClass` | 0/2 | 0/1 | 0/1 | 355,356 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getNav` | 0/24 | 0/26 | 0/39 | 64,65,66,67,69,70,71,74,75,77,80,83,84,85,89,90,91,95,96,97,100,101,102,104 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->moduleURL` | 0/2 | 0/1 | 0/0 | 133,135 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->module_installed` | 0/1 | 0/1 | 0/0 | 524 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->assign_variables` | 0/1 | 0/1 | 0/0 | 385 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->url_title` | 0/1 | 0/1 | 0/0 | 375 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->cpURL` | 0/13 | 0/14 | 0/20 | 108,109,112,113,116,117,118,119,120,121,122,126,128 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->prep_url` | 0/1 | 0/1 | 0/0 | 395 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->initiateSettings` | 23/25 | 13/17 | 16/24 | 481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `{main}` | 49/180 | 0/1 | 0/1 | 42,43,47,48,49,50,53,64,65,66,67,69,70,71,74,75,77,80,83,84,85,89,90,91,95,96,97,100,101,102,104,108,109,112,113,116,117,118,119,120,121,122,126,128,133,135,141,143,144,147,148,151,152,154,158,160,162,169,170,171,172,174,186,187,188,191,192,195,196,200,231,259,260,261,262,264,268,269,270,272,276,277,278,282,295,298,299,300,301,303,307,308,311,314,316,317,322,323,331,332,344,345,355,356,365,375,385,395,407,408,409,410,411,412,413,415,416,419,428,430,431,434,439,448,449,481,482,519,520,524,529 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getAddonThemesDir` | 0/2 | 0/4 | 0/4 | 331,332 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getCurrentPage` | 0/5 | 0/5 | 0/6 | 169,170,171,172,174 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->view` | 0/11 | 0/8 | 0/10 | 141,143,144,147,148,151,152,154,158,160,162 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getFooter` | 0/1 | 0/1 | 0/0 | 439 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->instantiate` | 0/2 | 0/1 | 0/1 | 42,43 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->reduce_double_slashes` | 0/2 | 0/1 | 0/0 | 448,449 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->pagination` | 0/8 | 0/5 | 0/6 | 186,187,188,191,192,195,196,200 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->cache` | 0/13 | 0/14 | 0/24 | 259,260,261,262,264,268,269,270,272,276,277,278,282 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getCachePath` | 0/4 | 0/3 | 0/3 | 428,430,431,434 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getCurrentUrlInfo` | 0/10 | 0/8 | 0/10 | 407,408,409,410,411,412,413,415,416,419 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->javascript_to_page` | 0/2 | 0/1 | 0/1 | 519,520 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->overwriteEEClass` | 0/2 | 0/1 | 0/1 | 344,345 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->reduce_double_slashes` | 0/4 | 0/3 | 0/3 | 408,409,411,412 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getCurrentPage` | 0/6 | 0/5 | 0/6 | 176,177,178,179,180,182 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->assign_parameters` | 0/1 | 0/1 | 0/0 | 499 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getAddonThemesDir` | 0/2 | 0/4 | 0/4 | 313,314 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->pagination` | 0/5 | 0/3 | 0/3 | 194,195,198,199,200 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getFooter` | 0/1 | 0/1 | 0/0 | 399 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->overwriteEEClass` | 0/2 | 0/1 | 0/1 | 326,327 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->instantiate` | 0/2 | 0/1 | 0/1 | 34,35 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->initiateSettings` | 23/25 | 13/17 | 16/24 | 444,445 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `{main}` | 41/195 | 0/1 | 0/1 | 34,35,39,40,41,42,45,47,49,51,54,55,56,57,58,59,60,61,65,67,68,71,72,75,80,81,82,83,86,89,90,94,95,96,97,98,99,100,102,104,105,106,107,109,111,112,113,115,117,118,119,120,122,124,125,126,129,131,132,135,136,139,144,146,147,150,151,154,160,162,163,164,170,176,177,178,179,180,182,194,195,198,199,200,230,236,237,239,240,242,243,244,246,257,258,262,263,264,265,266,267,269,273,274,275,277,281,282,283,284,286,288,291,293,304,305,313,314,326,327,337,338,347,357,367,368,369,370,371,372,373,375,376,379,388,390,391,394,399,408,409,411,412,444,445,482,483,487,488,490,491,493,499,509 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 0/22 | 0/26 | 0/39 | 39,40,41,42,45,47,49,51,54,55,56,57,58,59,60,61,65,67,68,71,72,75 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getNav` | 0/7 | 0/6 | 0/9 | 80,81,82,83,86,89,90 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 0/5 | 0/5 | 0/6 | 160,162,163,164,170 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 0/21 | 0/26 | 0/42 | 257,258,262,263,264,265,266,267,269,273,274,275,277,281,282,283,284,286,288,291,293 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->url_title` | 0/1 | 0/1 | 0/0 | 357 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getCurrentUrlInfo` | 0/10 | 0/8 | 0/10 | 367,368,369,370,371,372,373,375,376,379 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 0/31 | 0/28 | 0/46 | 94,95,96,97,98,99,100,102,104,105,106,107,109,111,112,113,115,117,118,119,120,122,124,125,126,129,131,132,135,136,139 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->xss_clean` | 0/1 | 0/1 | 0/0 | 347 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->flashData` | 0/2 | 0/1 | 0/1 | 304,305 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->module_installed` | 0/5 | 0/3 | 0/3 | 487,488,490,491,493 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->assign_variables` | 0/1 | 0/1 | 0/0 | 509 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->setConfig` | 7/16 | 3/7 | 2/10 | 230,236,237,239,240,242,243,244,246 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->removeEEClass` | 0/2 | 0/1 | 0/1 | 337,338 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 0/6 | 0/6 | 0/8 | 144,146,147,150,151,154 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getCachePath` | 0/4 | 0/3 | 0/3 | 388,390,391,394 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->javascript_to_page` | 0/2 | 0/1 | 0/1 | 482,483 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!stub` | `Extract_low_random->_unpack` | 0/20 | 0/4 | 0/4 | 215,216,217,218,219,220,221,223,224,225,226,227,228,229,230,231,232,233,234,236 |
| `low_random/low_random.phar!stub` | `Extract_low_random->_removeTmpFiles` | 0/10 | 0/12 | 0/18 | 274,276,277,278,279,280,285,286,287,288 |
| `low_random/low_random.phar!stub` | `Extract_low_random->extractFile` | 0/18 | 0/14 | 0/19 | 241,242,244,245,246,247,249,250,254,255,256,257,260,261,262,265,266,269 |
| `low_random/low_random.phar!stub` | `{main}` | 7/154 | 0/30 | 0/45 | 13,14,15,58,59,61,62,63,64,65,67,68,69,70,71,72,74,75,76,77,78,80,81,82,83,84,85,87,88,89,90,92,93,94,96,97,98,99,115,116,117,118,121,122,123,125,126,127,129,131,132,135,136,138,139,141,145,146,148,152,154,155,156,157,158,159,161,164,165,166,167,168,170,171,172,173,175,176,177,178,180,181,183,184,189,191,192,194,198,199,200,202,203,205,207,208,210,215,216,217,218,219,220,221,223,224,225,226,227,228,229,230,231,232,233,234,236,241,242,244,245,246,247,249,250,254,255,256,257,260,261,262,265,266,269,274,276,277,278,279,280,285,286,287,288,291,292 |
| `low_random/low_random.phar!stub` | `Extract_low_random->tmpdir` | 0/9 | 0/14 | 0/19 | 198,199,200,202,203,205,207,208,210 |
| `low_random/low_random.phar!stub` | `Extract_low_random->go` | 0/50 | 0/35 | 0/53 | 115,116,117,118,121,122,123,125,126,127,129,131,132,135,136,138,139,141,145,146,148,152,154,155,156,157,158,159,161,164,165,166,167,168,170,171,172,173,175,176,177,178,180,181,183,184,189,191,192,194 |
| `low_random/low_random.phar!/index.php` | `{closure:phar:///Users/tomjaeger/Sites/add-ons/low_random/low_random/low_random.phar/index.php:3-28}` | 6/12 | 2/5 | 1/7 | 14,17,18,21,24,26 |
| `low_random/low_random.phar!/index.php` | `{main}` | 7/13 | 0/1 | 0/1 | 14,17,18,21,24,26 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE6.php` | `{main}` | 2/2 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getNav` | 0/24 | 0/26 | 0/39 | 64,65,66,67,69,70,71,74,75,77,80,83,84,85,89,90,91,95,96,97,100,101,102,104 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getBaseURL` | 0/5 | 0/5 | 0/6 | 47,48,49,50,53 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getAddonThemesDir` | 0/2 | 0/4 | 0/4 | 331,332 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getFooter` | 0/1 | 0/1 | 0/0 | 439 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->module_installed` | 0/1 | 0/1 | 0/0 | 524 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->reduce_double_slashes` | 0/2 | 0/1 | 0/0 | 448,449 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->instantiate` | 0/2 | 0/1 | 0/1 | 42,43 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->cache` | 0/13 | 0/14 | 0/24 | 259,260,261,262,264,268,269,270,272,276,277,278,282 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->assign_variables` | 0/1 | 0/1 | 0/0 | 385 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getCurrentUrlInfo` | 6/10 | 1/8 | 0/10 | 413,415,416,419 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->assign_parameters` | 0/1 | 0/1 | 0/0 | 529 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->initiateSettings` | 23/25 | 13/17 | 16/24 | 481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->flashData` | 0/14 | 0/13 | 0/19 | 295,298,299,300,301,303,307,308,311,314,316,317,322,323 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->xss_clean` | 0/1 | 0/1 | 0/0 | 365 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->prep_url` | 0/1 | 0/1 | 0/0 | 395 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `{main}` | 56/181 | 0/1 | 0/1 | 42,43,47,48,49,50,53,64,65,66,67,69,70,71,74,75,77,80,83,84,85,89,90,91,95,96,97,100,101,102,104,108,109,112,113,116,117,118,119,120,121,122,126,128,133,135,141,143,144,147,148,151,152,154,158,160,162,169,170,171,172,174,186,187,188,191,192,195,196,200,231,259,260,261,262,264,268,269,270,272,276,277,278,282,295,298,299,300,301,303,307,308,311,314,316,317,322,323,331,332,344,345,355,356,365,375,385,395,413,415,416,419,428,430,431,434,439,448,449,481,482,519,520,524,529 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getCurrentPage` | 0/5 | 0/5 | 0/6 | 169,170,171,172,174 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->view` | 0/11 | 0/8 | 0/10 | 141,143,144,147,148,151,152,154,158,160,162 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->setConfig` | 15/16 | 6/7 | 7/10 | 231 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->removeEEClass` | 0/2 | 0/1 | 0/1 | 355,356 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->moduleURL` | 0/2 | 0/1 | 0/0 | 133,135 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->pagination` | 0/8 | 0/5 | 0/6 | 186,187,188,191,192,195,196,200 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->url_title` | 0/1 | 0/1 | 0/0 | 375 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getCachePath` | 0/4 | 0/3 | 0/3 | 428,430,431,434 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->overwriteEEClass` | 0/2 | 0/1 | 0/1 | 344,345 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->cpURL` | 0/13 | 0/14 | 0/20 | 108,109,112,113,116,117,118,119,120,121,122,126,128 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->javascript_to_page` | 0/2 | 0/1 | 0/1 | 519,520 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->instantiate` | 0/2 | 0/1 | 0/1 | 42,43 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getCurrentPage` | 0/5 | 0/5 | 0/6 | 169,170,171,172,174 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->cache` | 0/13 | 0/14 | 0/24 | 259,260,261,262,264,268,269,270,272,276,277,278,282 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->url_title` | 0/1 | 0/1 | 0/0 | 375 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->cpURL` | 0/13 | 0/14 | 0/20 | 108,109,112,113,116,117,118,119,120,121,122,126,128 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getCurrentUrlInfo` | 0/10 | 0/8 | 0/10 | 407,408,409,410,411,412,413,415,416,419 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->flashData` | 0/14 | 0/13 | 0/19 | 295,298,299,300,301,303,307,308,311,314,316,317,322,323 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->xss_clean` | 0/1 | 0/1 | 0/0 | 365 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->pagination` | 0/8 | 0/5 | 0/6 | 186,187,188,191,192,195,196,200 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->setConfig` | 15/16 | 6/7 | 7/10 | 231 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->removeEEClass` | 0/2 | 0/1 | 0/1 | 355,356 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->moduleURL` | 0/2 | 0/1 | 0/0 | 133,135 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->overwriteEEClass` | 0/2 | 0/1 | 0/1 | 344,345 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->reduce_double_slashes` | 0/2 | 0/1 | 0/0 | 448,449 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->initiateSettings` | 23/25 | 13/17 | 16/24 | 481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->prep_url` | 0/1 | 0/1 | 0/0 | 395 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `{main}` | 49/180 | 0/1 | 0/1 | 42,43,47,48,49,50,53,64,65,66,67,69,70,71,74,75,77,80,83,84,85,89,90,91,95,96,97,100,101,102,104,108,109,112,113,116,117,118,119,120,121,122,126,128,133,135,141,143,144,147,148,151,152,154,158,160,162,169,170,171,172,174,186,187,188,191,192,195,196,200,231,259,260,261,262,264,268,269,270,272,276,277,278,282,295,298,299,300,301,303,307,308,311,314,316,317,322,323,331,332,344,345,355,356,365,375,385,395,407,408,409,410,411,412,413,415,416,419,428,430,431,434,439,448,449,481,482,519,520,524,529 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->view` | 0/11 | 0/8 | 0/10 | 141,143,144,147,148,151,152,154,158,160,162 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getNav` | 0/24 | 0/26 | 0/39 | 64,65,66,67,69,70,71,74,75,77,80,83,84,85,89,90,91,95,96,97,100,101,102,104 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getBaseURL` | 0/5 | 0/5 | 0/6 | 47,48,49,50,53 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->module_installed` | 0/1 | 0/1 | 0/0 | 524 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->assign_parameters` | 0/1 | 0/1 | 0/0 | 529 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getAddonThemesDir` | 0/2 | 0/4 | 0/4 | 331,332 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->assign_variables` | 0/1 | 0/1 | 0/0 | 385 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getCachePath` | 0/4 | 0/3 | 0/3 | 428,430,431,434 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getFooter` | 0/1 | 0/1 | 0/0 | 439 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->javascript_to_page` | 0/2 | 0/1 | 0/1 | 519,520 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->set` | 8/12 | 3/5 | 2/6 | 58,67,68,70 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->get` | 12/14 | 5/7 | 4/9 | 24,28 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->delete` | 6/7 | 4/5 | 4/6 | 91 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->createKey` | 3/3 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->prepCacheData` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->serveCacheData` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `{main}` | 50/70 | 0/1 | 0/1 | 24,28,58,67,68,70,91,125,197,198,199,208,209,210,216,217,218,228,229,230 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->clear` | 6/7 | 7/8 | 8/11 | 125 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->has` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/PersistentCache.php` | `Low\Random\FluxCapacitor\Conduit\PersistentCache->checkPath` | 10/22 | 9/13 | 10/18 | 197,198,199,208,209,210,216,217,218,228,229,230 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->getEEVersion` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->ver_gt` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->ver_gte` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->ver_lte` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `{main}` | 13/13 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->is_ee3` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->is_ee2` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->is_ee5` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->is_ee4` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/AbstractBridge.php` | `Low\Random\FluxCapacitor\Bridge\AbstractBridge->ver_lt` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->__construct` | 4/6 | 4/5 | 3/7 | 28,29 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->getEntryVariables` | 13/13 | 6/6 | 7/7 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->publish_tabs` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `{main}` | 22/24 | 0/1 | 0/1 | 28,29 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->save` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->display` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->publish_data_db` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->getSetting` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `{main}` | 23/23 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->updateSetting` | 8/8 | 4/4 | 3/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->getSettings` | 7/7 | 4/4 | 3/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `__construct` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `activateExtension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `updateExtension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `disableExtension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `updateVersion` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `registerExtension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `unregisterExtension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `cpCustomMenu` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `disable_extension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `activate_extension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `update_extension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `update_version` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `register_extension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `unregister_extension` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `cp_custom_menu` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `__construct` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `update` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `accepts_content_type` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `getChannelId` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `displaySettingsRow` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `displaySettingsReturn` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `_display_settings_add_row` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `_package_display_settings` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `__construct` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `index` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `license` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `save_license` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `paginateModel` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `canIncludeNav` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `__construct` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `install` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `update` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `uninstall` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `__construct` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `generateNav` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `formatItems` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `itemsToNav` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `buttonsToItems` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `setActive` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `setBreadcrumb` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `getActiveItem` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `deferGenerate` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `preGenerateNav` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `postGenerateNav` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `getUrl` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `defaultItems` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `defaultButtons` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `defaultActiveMap` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `setToolbarIcon` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `__construct` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `keyExistsForAddon` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `saveLicenseKey` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `deleteLicenseKey` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `verifyLicenseTable` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `check` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `getLicenseKey` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `getIgnoreSite` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `getOpenSSLPublicKey` | unknown | unknown | unknown | not instrumented |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `spiderSafe` | unknown | unknown | unknown | not instrumented |

Procedural code and file guards are represented by `{main}` entries. The PHAR stub is independently inventoried. Pi::info line 30 throws before later array entries can execute; strict fallback extraction stops at stub line 177. Windows-only stub paths cannot be covered on this macOS host. Source changes are not authorized.
