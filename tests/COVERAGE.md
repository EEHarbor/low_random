# Coverage checkpoint

Incomplete phase 4. Main process: PHP 8.2.30 / Xdebug 3.5.3. Pinned source baseline: `892b6dae8282b159e580e3c1a6076a39d9902f84`. The complete 207-case suites verify all 27 shipped source hashes and native compilation; production is unchanged.

`BRANCH_RAW_PHP74.json` and `BRANCH_RAW_PHP82.json` retain exact native lines, basic blocks and unchanged outgoing exit counters for the current main processes. All 128 main/native reports (64 per runtime) remain separate in `tests/reports/phase4-final-qfanmdlw/`. `contexts.json` records each report SHA256, original archive/source root, PHP/Xdebug/EE/request/native context, locale, OPcache and PHAR-stream provenance. Corresponding `*.summary.json` files retain every method's line/basic-block/exit observations, exact uncovered exits, and unknown sources. No runtime, process or conditional graph is merged.

`unknown` means absent instrumentation and never 100%. Lines use Xdebug opcode intervals rather than AST method ranges; broad `{main}` intervals may overlap method bodies and must not be added to method totals. Basic-block hits do not prove all conditional/loop outcomes. Every original `out`/`out_hit` is retained, including synthetic terminal counters that can remain zero despite observed returns. Full path coverage is not required. Current main summaries have 262 graph/function rows across all inventory units.

New legacy probes directly require the unchanged AbstractBridge, StaticCache and EE2 library entries from the original PHAR. They isolate historical APP_VER constants, sessions, cache services and a temporary PATH_THIRD view directory. This bypasses package-loader resolution and does not boot historical EE or real SQL/CP integration. No production installation receives a dynamic view. The 48 new native graphs remain separate, including the persistent-delete regression; existing navigation/import/crypto/compiler contexts are freshly rerun without graph merging.

Separate Clover: `tests/reports/phase4-final-qfanmdlw/clover82.xml`; its separate JUnit is `clover82.junit.xml`, and `methods82.json` is the skill line-only report. A trial command assigned both output types to one path; that trial was rejected and Clover was rerun with separate paths before this checkpoint. The eight ordinary plugin methods retain 100% executable lines with unknown branches, which is not coverage closure.

The current EE database service creates fresh builders, preserving primary pending queries/cache. EE2 settings instead clone the primary builder; the legacy tests assert their owning journal tail, immediate site predicate before UPDATE, primary cache preservation and exactly one DML mutation. Object ids are used only within the live writer trace, because destroyed clones can reuse ids.

| Source | Function/method | Lines | Basic blocks | Outgoing exits | Uncovered executable lines |
|---|---|---|---|---|---|
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->disable_extension` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->update_version` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->activate_extension` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->cpCustomMenu` | 2/4 | 2/3 | 1/4 | 156,157 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->cp_custom_menu` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->registerExtension` | 20/20 | 9/9 | 11/12 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->__construct` | 6/6 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->unregisterExtension` | 7/7 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->updateExtension` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->update_extension` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `{main}` | 59/61 | 0/1 | 0/1 | 156,157 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->register_extension` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->disableExtension` | 6/6 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->unregister_extension` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->activateExtension` | 3/3 | 4/4 | 4/5 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ext.php` | `Low\Random\FluxCapacitor\Base\Ext->updateVersion` | 5/5 | 1/1 | 0/0 | none |
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
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->getOpenSSLPublicKey` | 0/1 | 0/1 | 0/0 | 216 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->getIgnoreSite` | 7/7 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->getLicenseKey` | 8/8 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `{main}` | 61/97 | 0/1 | 0/1 | 131,132,134,137,139,141,142,143,144,145,146,147,148,149,150,151,152,156,159,161,163,164,167,168,170,174,176,180,181,183,216,221,222,223,224,226 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->keyExistsForAddon` | 7/7 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->saveLicenseKey` | 9/9 | 10/10 | 12/13 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->check` | 0/30 | 0/16 | 0/23 | 131,132,134,137,139,141,142,143,144,145,146,147,148,149,150,151,152,156,159,161,163,164,167,168,170,174,176,180,181,183 |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->verifyLicenseTable` | 22/22 | 14/14 | 19/20 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->deleteLicenseKey` | 4/4 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/Version.php` | `Low\Random\FluxCapacitor\Conduit\Version->spiderSafe` | 0/5 | 0/4 | 0/4 | 221,222,223,224,226 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `Low\Random\FluxCapacitor\Base\Upd->uninstall` | 18/18 | 5/6 | 4/7 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `Low\Random\FluxCapacitor\Base\Upd->__construct` | 0/6 | 0/1 | 0/1 | 28,29,30,32,33,34 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `{main}` | 33/44 | 0/1 | 0/1 | 28,29,30,32,33,34,66,68,69,70,75 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `Low\Random\FluxCapacitor\Base\Upd->update` | 4/9 | 6/9 | 5/12 | 66,68,69,70,75 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Upd.php` | `Low\Random\FluxCapacitor\Base\Upd->install` | 10/10 | 3/3 | 2/3 | none |
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
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->xss_clean` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->flashData` | 14/14 | 13/13 | 17/19 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->assign_parameters` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getBaseURL` | 5/5 | 5/5 | 6/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->setConfig` | 15/16 | 6/7 | 7/10 | 231 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->removeEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getNav` | 24/24 | 26/26 | 36/39 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->moduleURL` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->module_installed` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->assign_variables` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->url_title` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->cpURL` | 13/13 | 14/14 | 20/20 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->prep_url` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->initiateSettings` | 22/25 | 12/17 | 14/24 | 472,481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `{main}` | 177/181 | 0/1 | 0/1 | 231,472,481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getCurrentPage` | 5/5 | 5/5 | 4/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->view` | 11/11 | 8/8 | 10/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getFooter` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->instantiate` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->reduce_double_slashes` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->pagination` | 8/8 | 5/5 | 6/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->cache` | 13/13 | 14/14 | 14/24 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getCachePath` | 4/4 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->getCurrentUrlInfo` | 10/10 | 8/8 | 9/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->javascript_to_page` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE3.php` | `Low\Random\FluxCapacitor\Bridge\EE3->overwriteEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->reduce_double_slashes` | 0/4 | 0/3 | 0/3 | 408,409,411,412 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getCurrentPage` | 6/6 | 5/5 | 4/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->assign_parameters` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getAddonThemesDir` | 0/2 | 0/4 | 0/4 | 313,314 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->pagination` | 5/5 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getFooter` | 0/1 | 0/1 | 0/0 | 399 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->overwriteEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->instantiate` | 0/2 | 0/1 | 0/1 | 34,35 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->initiateSettings` | 25/25 | 17/17 | 22/24 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `{main}` | 72/195 | 0/1 | 0/1 | 34,35,39,40,41,42,45,47,49,51,54,55,56,57,58,59,60,61,65,67,68,71,72,75,80,81,82,83,86,89,90,94,95,96,97,98,99,100,102,104,105,106,107,109,111,112,113,115,117,118,119,120,122,124,125,126,129,131,132,135,136,139,144,146,147,150,151,154,160,162,163,164,170,257,258,262,263,264,265,266,267,269,273,274,275,277,281,282,283,284,286,288,291,293,313,314,347,367,368,369,370,371,372,373,375,376,379,388,390,391,394,399,408,409,411,412,482,483,487,488,490,491,493 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 0/22 | 0/26 | 0/39 | 39,40,41,42,45,47,49,51,54,55,56,57,58,59,60,61,65,67,68,71,72,75 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getNav` | 0/7 | 0/6 | 0/9 | 80,81,82,83,86,89,90 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 0/5 | 0/5 | 0/6 | 160,162,163,164,170 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 0/21 | 0/26 | 0/42 | 257,258,262,263,264,265,266,267,269,273,274,275,277,281,282,283,284,286,288,291,293 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->url_title` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getCurrentUrlInfo` | 0/10 | 0/8 | 0/10 | 367,368,369,370,371,372,373,375,376,379 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 0/31 | 0/28 | 0/46 | 94,95,96,97,98,99,100,102,104,105,106,107,109,111,112,113,115,117,118,119,120,122,124,125,126,129,131,132,135,136,139 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->xss_clean` | 0/1 | 0/1 | 0/0 | 347 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->flashData` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->module_installed` | 0/5 | 0/3 | 0/3 | 487,488,490,491,493 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->assign_variables` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->setConfig` | 16/16 | 7/7 | 9/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE2.php` | `Low\Random\FluxCapacitor\Bridge\EE2->removeEEClass` | 2/2 | 1/1 | 0/1 | none |
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
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE6.php` | `{main}` | 1/1 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getNav` | 24/24 | 26/26 | 36/39 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getBaseURL` | 5/5 | 5/5 | 6/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getFooter` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->module_installed` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->reduce_double_slashes` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->instantiate` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->cache` | 13/13 | 14/14 | 14/24 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->assign_variables` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getCurrentUrlInfo` | 10/10 | 8/8 | 9/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->assign_parameters` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->initiateSettings` | 22/25 | 12/17 | 14/24 | 472,481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->flashData` | 14/14 | 13/13 | 17/19 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->xss_clean` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->prep_url` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `{main}` | 176/180 | 0/1 | 0/1 | 231,472,481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getCurrentPage` | 5/5 | 5/5 | 4/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->view` | 11/11 | 8/8 | 10/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->setConfig` | 15/16 | 6/7 | 7/10 | 231 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->removeEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->moduleURL` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->pagination` | 8/8 | 5/5 | 6/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->url_title` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getCachePath` | 4/4 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->overwriteEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->cpURL` | 13/13 | 14/14 | 20/20 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE5.php` | `Low\Random\FluxCapacitor\Bridge\EE5->javascript_to_page` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->instantiate` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getCurrentPage` | 5/5 | 5/5 | 4/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->cache` | 13/13 | 14/14 | 14/24 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->url_title` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getSettings` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->cpURL` | 13/13 | 14/14 | 20/20 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getCurrentUrlInfo` | 10/10 | 8/8 | 9/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getStartNum` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->flashData` | 14/14 | 13/13 | 17/19 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->xss_clean` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->pagination` | 8/8 | 5/5 | 6/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->setConfig` | 15/16 | 6/7 | 7/10 | 231 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->removeEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->moduleURL` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->overwriteEEClass` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getConfig` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->reduce_double_slashes` | 2/2 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->initiateSettings` | 22/25 | 12/17 | 14/24 | 472,481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->prep_url` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `{main}` | 176/180 | 0/1 | 0/1 | 231,472,481,482 |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->view` | 11/11 | 8/8 | 10/10 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getNav` | 24/24 | 26/26 | 36/39 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getBaseURL` | 5/5 | 5/5 | 6/6 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->module_installed` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->assign_parameters` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getAddonThemesDir` | 2/2 | 3/4 | 2/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->assign_variables` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getCachePath` | 4/4 | 3/3 | 3/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->getFooter` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Bridge/EE4.php` | `Low\Random\FluxCapacitor\Bridge\EE4->javascript_to_page` | 2/2 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `Low\Random\FluxCapacitor\Base\Mcp->paginateModel` | 16/18 | 11/12 | 10/16 | 147,174 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `Low\Random\FluxCapacitor\Base\Mcp->license` | 14/14 | 6/6 | 7/7 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `Low\Random\FluxCapacitor\Base\Mcp->canIncludeNav` | 2/6 | 4/8 | 3/11 | 186,187,189,190 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `{main}` | 73/83 | 0/1 | 0/1 | 34,106,116,137,147,174,186,187,189,190 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `Low\Random\FluxCapacitor\Base\Mcp->index` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `Low\Random\FluxCapacitor\Base\Mcp->save_license` | 35/38 | 19/19 | 23/28 | 106,116,137 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Mcp.php` | `Low\Random\FluxCapacitor\Base\Mcp->__construct` | 4/5 | 2/3 | 1/4 | 34 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->getChannelId` | 12/13 | 12/13 | 16/18 | 74 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->_package_display_settings` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->displaySettingsReturn` | 6/7 | 2/3 | 1/3 | 135 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->_display_settings_add_row` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `{main}` | 42/56 | 0/12 | 0/18 | 9,11,13,15,16,55,56,74,106,107,109,110,111,135 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->accepts_content_type` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->update` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->displaySettingsRow` | 8/13 | 3/7 | 2/9 | 106,107,109,110,111 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Ft.php` | `Low\Random\FluxCapacitor\Base\Ft->__construct` | 5/7 | 4/5 | 3/7 | 55,56 |
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
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->getSetting` | 4/4 | 3/3 | 2/3 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->__construct` | 3/3 | 1/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `{main}` | 23/23 | 0/1 | 0/1 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->updateSetting` | 8/8 | 4/4 | 3/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/GlobalFieldSettings.php` | `Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings->getSettings` | 7/7 | 4/4 | 3/4 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->__construct` | 4/6 | 4/5 | 3/7 | 28,29 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->getEntryVariables` | 13/13 | 6/6 | 7/7 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->publish_tabs` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `{main}` | 22/24 | 0/1 | 0/1 | 28,29 |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->save` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->display` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Base/Tab.php` | `Low\Random\FluxCapacitor\Base\Tab->publish_data_db` | 1/1 | 1/1 | 0/0 | none |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `__construct` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `generateNav` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `formatItems` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `itemsToNav` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `buttonsToItems` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `setActive` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `setBreadcrumb` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `getActiveItem` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `deferGenerate` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `preGenerateNav` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `postGenerateNav` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `getUrl` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `defaultItems` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `defaultButtons` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `defaultActiveMap` | unknown | unknown | unknown | unknown |
| `low_random/low_random.phar!/FluxCapacitor/Conduit/McpNav.php` | `setToolbarIcon` | unknown | unknown | unknown | unknown |

## Separate native legacy observations

Only executed EE2 methods are shown below; complete per-context summaries retain all unexecuted methods and unknowns. Repeated mode labels identify distinct test vectors by their unique report paths, and their graphs are never combined.

| PHP | APP_VER | Mode | Raw report | Method | Lines | Basic blocks | Outgoing exits |
|---|---|---|---|---|---|---|---|
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79400.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79400.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 15/22 | 16/26 | 16/39 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79400.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79408.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79408.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 17/22 | 18/26 | 18/39 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79408.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79421.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79421.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 18/22 | 19/26 | 19/39 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79421.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79427.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79427.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 16/22 | 15/26 | 15/39 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79427.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79430.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79430.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 15/22 | 18/26 | 18/39 |
| 7.4.33 | 2.5.5 | legacy-bridge/url | `raw74.79430.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79437.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79437.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 10/28 | 10/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79439.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79439.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 10/31 | 8/28 | 7/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79441.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79441.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 13/28 | 13/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79454.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79454.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 14/28 | 14/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79458.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79458.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 11/31 | 12/28 | 11/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79460.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79460.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 12/31 | 12/28 | 11/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79463.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79463.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 11/31 | 11/28 | 10/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79466.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79466.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 14/31 | 13/28 | 12/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79469.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79469.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 12/28 | 11/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79474.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79474.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 12/31 | 11/28 | 10/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79479.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/route | `raw74.79479.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 11/31 | 12/28 | 12/46 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79483.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79483.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 5/5 | 5/5 | 4/6 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79490.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79490.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 3/5 | 4/5 | 3/6 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79497.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79497.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 3/5 | 3/5 | 2/6 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79503.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.5.5 | legacy-bridge/view | `raw74.79503.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 3/5 | 3/5 | 2/6 |
| 7.4.33 | 2.7.3 | legacy-bridge/cache | `raw74.79508.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.7.3 | legacy-bridge/cache | `raw74.79508.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 18/21 | 23/26 | 23/42 |
| 7.4.33 | 2.8.0 | legacy-bridge/cache | `raw74.79513.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.8.0 | legacy-bridge/cache | `raw74.79513.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 18/21 | 21/26 | 21/42 |
| 7.4.33 | 2.8.0 | legacy-bridge/cache | `raw74.79518.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.8.0 | legacy-bridge/cache | `raw74.79518.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 18/21 | 21/26 | 21/42 |
| 7.4.33 | 2.8.0 | legacy-bridge/cache | `raw74.79521.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 7.4.33 | 2.8.0 | legacy-bridge/cache | `raw74.79521.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 16/21 | 21/26 | 21/42 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79481.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79481.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 15/22 | 16/26 | 16/39 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79481.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79486.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79486.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 17/22 | 18/26 | 18/39 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79486.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79491.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79491.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 18/22 | 19/26 | 19/39 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79491.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79496.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79496.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 16/22 | 15/26 | 15/39 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79496.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79502.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79502.json` | `Low\Random\FluxCapacitor\Bridge\EE2->getBaseURL` | 15/22 | 18/26 | 18/39 |
| 8.2.30 | 2.5.5 | legacy-bridge/url | `raw82.79502.json` | `Low\Random\FluxCapacitor\Bridge\EE2->moduleURL` | 5/6 | 5/6 | 5/8 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79507.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79507.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 10/28 | 10/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79511.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79511.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 10/31 | 8/28 | 7/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79516.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79516.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 13/28 | 13/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79519.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79519.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 14/28 | 14/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79523.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79523.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 11/31 | 12/28 | 11/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79526.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79526.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 12/31 | 12/28 | 11/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79530.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79530.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 11/31 | 11/28 | 10/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79534.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79534.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 14/31 | 13/28 | 12/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79539.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79539.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 13/31 | 12/28 | 11/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79543.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79543.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 12/31 | 11/28 | 10/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79547.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/route | `raw82.79547.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cpURL` | 11/31 | 12/28 | 12/46 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79559.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79559.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 5/5 | 5/5 | 4/6 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79563.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79563.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 3/5 | 4/5 | 3/6 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79568.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79568.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 3/5 | 3/5 | 2/6 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79572.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.5.5 | legacy-bridge/view | `raw82.79572.json` | `Low\Random\FluxCapacitor\Bridge\EE2->view` | 3/5 | 3/5 | 2/6 |
| 8.2.30 | 2.7.3 | legacy-bridge/cache | `raw82.79577.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.7.3 | legacy-bridge/cache | `raw82.79577.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 18/21 | 23/26 | 23/42 |
| 8.2.30 | 2.8.0 | legacy-bridge/cache | `raw82.79582.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.8.0 | legacy-bridge/cache | `raw82.79582.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 18/21 | 21/26 | 21/42 |
| 8.2.30 | 2.8.0 | legacy-bridge/cache | `raw82.79598.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.8.0 | legacy-bridge/cache | `raw82.79598.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 18/21 | 21/26 | 21/42 |
| 8.2.30 | 2.8.0 | legacy-bridge/cache | `raw82.79602.json` | `Low\Random\FluxCapacitor\Bridge\EE2->__construct` | 3/3 | 1/1 | 0/1 |
| 8.2.30 | 2.8.0 | legacy-bridge/cache | `raw82.79602.json` | `Low\Random\FluxCapacitor\Bridge\EE2->cache` | 16/21 | 21/26 | 21/42 |
