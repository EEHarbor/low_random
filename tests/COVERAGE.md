# Coverage checkpoint

Incomplete phase 5. Main process: PHP 8.2.30 / Xdebug 3.5.3. Pinned source baseline: `892b6dae8282b159e580e3c1a6076a39d9902f84`. Complete 236-case suites verify all 27 shipped source hashes and native compilation. No product source or archive changed.

`BRANCH_RAW_PHP74.json` and `BRANCH_RAW_PHP82.json` retain the exact current main-process native lines, basic blocks and unchanged outgoing exits. All 182 main/native graphs (91 per runtime) remain separate in `tests/reports/phase5-final-ey7o8wc3/`. `contexts.json` retains every raw SHA256 and PHP/Xdebug/EE/request/native case, original archive/source root, locale, OPcache and PHAR-stream provenance. Every graph has a full `*.summary.json` listing exact uncovered exits and unknown sources. No process/runtime/conditional graphs are merged; previous native scenarios are freshly rerun.

Unknown or absent branch instrumentation is never 100%. Basic-block hits do not establish all conditional/loop outcomes. Original `out`/`out_hit` includes synthetic terminal counters, whose unobserved values remain unresolved. Function lines use Xdebug opcode intervals rather than AST ranges; broad `{main}` intervals may overlap method bodies and must not be added to method totals. Full execution path 100% is not required. The main report has 262 function/graph rows across every inventory unit.

The normal original archive loader declares Extract_low_random. In-process parser tests compare all 22 original manifest entries and extracted bodies with native Phar, including sizes, timestamps, unsigned CRCs, offsets and final stream positions. Synthetic alias/metadata/flag bytes are parser inputs, never an edited archive. The main _unpack method now observes 20/20 lines, 4/4 basic blocks and4/4 outgoing exits. Main extractFile has 13/18 lines, 10/14 blocks and 11/19 outgoing exits; native codec and termination graphs remain separate below rather than being merged into a larger percentage.

Fifty-four new native graphs use the original declared extraction helper. Eighteen deterministic high-entropy binary vectors span 0/1/8191/8192/8193/20000 bytes with native plain/gzip/bzip2 data. Encoded-size assertions prove the large compressed inputs cross the actual 8 KiB source read boundary. Checksum termination succeeds; malformed size reporting exposes undefined$stat at stub 262. Native Unix TMPDIR checks include explicit, empty and unset values. Cleanup controls use only unique owned trees and finally restore the child CWD and delete owned remnants. Nonempty-child, dotfile and dangling-link cases remain ordinary red source regressions. Dotfile/link cases are explicitly robustness shapes, not claims the shipped archive normally creates them.

Separate Clover: `tests/reports/phase5-final-ey7o8wc3/clover82.xml`, separate JUnit `clover82.junit.xml`, skill line-only `methods82.json`. Eight ordinary plugin methods retain 100% executable lines with unknown branch fields. This is not source/file coverage closure. Synthetic exits, original stream-free loader diagnostics, immutable archive contents, platform-specific branches and broader behavior/integration gaps remain explicit.

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
| `low_random/low_random.phar!stub` | `Extract_low_random->_unpack` | 20/20 | 4/4 | 4/4 | none |
| `low_random/low_random.phar!stub` | `Extract_low_random->_removeTmpFiles` | 0/10 | 0/12 | 0/18 | 274,276,277,278,279,280,285,286,287,288 |
| `low_random/low_random.phar!stub` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 | 255,257,261,262,266 |
| `low_random/low_random.phar!stub` | `{main}` | 40/154 | 0/30 | 0/45 | 13,14,15,58,59,61,62,63,64,65,67,68,69,70,71,72,74,75,76,77,78,80,81,82,83,84,85,87,88,89,90,92,93,94,96,97,98,99,115,116,117,118,121,122,123,125,126,127,129,131,132,135,136,138,139,141,145,146,148,152,154,155,156,157,158,159,161,164,165,166,167,168,170,171,172,173,175,176,177,178,180,181,183,184,189,191,192,194,198,199,200,202,203,205,207,208,210,255,257,261,262,266,274,276,277,278,279,280,285,286,287,288,291,292 |
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

## Separate native stub observations

Only executed stub methods are shown. Complete per-context summaries retain all unexecuted methods and unknowns; these graphs are not combined.

| PHP | Native case | Raw report | Method | Lines | Basic blocks | Outgoing exits |
|---|---|---|---|---|---|---|
| 7.4.33 | stub-boundary/valid/plain/0 | `raw74.75227.json` | `Extract_low_random->extractFile` | 8/18 | 7/14 | 6/19 |
| 7.4.33 | stub-boundary/valid/gzip/0 | `raw74.75241.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 7.4.33 | stub-boundary/valid/bzip/0 | `raw74.75257.json` | `Extract_low_random->extractFile` | 12/18 | 10/14 | 10/19 |
| 7.4.33 | stub-boundary/valid/plain/1 | `raw74.75271.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 7.4.33 | stub-boundary/valid/gzip/1 | `raw74.75291.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 7.4.33 | stub-boundary/valid/bzip/1 | `raw74.75314.json` | `Extract_low_random->extractFile` | 12/18 | 10/14 | 10/19 |
| 7.4.33 | stub-boundary/valid/plain/8191 | `raw74.75330.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 7.4.33 | stub-boundary/valid/gzip/8191 | `raw74.75347.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 7.4.33 | stub-boundary/valid/bzip/8191 | `raw74.75393.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 7.4.33 | stub-boundary/valid/plain/8192 | `raw74.75409.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 7.4.33 | stub-boundary/valid/gzip/8192 | `raw74.75465.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 7.4.33 | stub-boundary/valid/bzip/8192 | `raw74.75479.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 7.4.33 | stub-boundary/valid/plain/8193 | `raw74.75500.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 7.4.33 | stub-boundary/valid/gzip/8193 | `raw74.75512.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 7.4.33 | stub-boundary/valid/bzip/8193 | `raw74.75537.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 7.4.33 | stub-boundary/valid/plain/20000 | `raw74.75552.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 7.4.33 | stub-boundary/valid/gzip/20000 | `raw74.75580.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 7.4.33 | stub-boundary/valid/bzip/20000 | `raw74.75604.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 7.4.33 | stub-boundary/checksum/plain/1 | `raw74.75621.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 7.4.33 | stub-boundary/size/plain/1 | `raw74.75633.json` | `Extract_low_random->extractFile` | 11/18 | 8/14 | 8/19 |
| 7.4.33 | stub-boundary/temporary-directory/none/none | `raw74.75650.json` | `Extract_low_random->tmpdir` | 4/9 | 4/14 | 3/19 |
| 7.4.33 | stub-boundary/cleanup/empty/none | `raw74.75668.json` | `Extract_low_random->_removeTmpFiles` | 6/10 | 3/12 | 2/18 |
| 7.4.33 | stub-boundary/cleanup/files/none | `raw74.75679.json` | `Extract_low_random->_removeTmpFiles` | 9/10 | 9/12 | 9/18 |
| 7.4.33 | stub-boundary/cleanup/empty-directory/none | `raw74.75691.json` | `Extract_low_random->_removeTmpFiles` | 9/10 | 9/12 | 9/18 |
| 7.4.33 | stub-boundary/cleanup/populated-directory/none | `raw74.75700.json` | `Extract_low_random->_removeTmpFiles` | 4/10 | 5/12 | 4/18 |
| 7.4.33 | stub-boundary/cleanup/dotfile/none | `raw74.75715.json` | `Extract_low_random->_removeTmpFiles` | 3/10 | 3/12 | 2/18 |
| 7.4.33 | stub-boundary/cleanup/dangling-link/none | `raw74.75727.json` | `Extract_low_random->_removeTmpFiles` | 4/10 | 5/12 | 5/18 |
| 8.2.30 | stub-boundary/valid/plain/0 | `raw82.75945.json` | `Extract_low_random->extractFile` | 8/18 | 7/14 | 6/19 |
| 8.2.30 | stub-boundary/valid/gzip/0 | `raw82.75956.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 8.2.30 | stub-boundary/valid/bzip/0 | `raw82.75971.json` | `Extract_low_random->extractFile` | 12/18 | 10/14 | 10/19 |
| 8.2.30 | stub-boundary/valid/plain/1 | `raw82.75989.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 8.2.30 | stub-boundary/valid/gzip/1 | `raw82.76001.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 8.2.30 | stub-boundary/valid/bzip/1 | `raw82.76012.json` | `Extract_low_random->extractFile` | 12/18 | 10/14 | 10/19 |
| 8.2.30 | stub-boundary/valid/plain/8191 | `raw82.76029.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 8.2.30 | stub-boundary/valid/gzip/8191 | `raw82.76041.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 8.2.30 | stub-boundary/valid/bzip/8191 | `raw82.76054.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 8.2.30 | stub-boundary/valid/plain/8192 | `raw82.76066.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 8.2.30 | stub-boundary/valid/gzip/8192 | `raw82.76076.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 8.2.30 | stub-boundary/valid/bzip/8192 | `raw82.76088.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 8.2.30 | stub-boundary/valid/plain/8193 | `raw82.76105.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 8.2.30 | stub-boundary/valid/gzip/8193 | `raw82.76129.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 8.2.30 | stub-boundary/valid/bzip/8193 | `raw82.76152.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 8.2.30 | stub-boundary/valid/plain/20000 | `raw82.76180.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 8.2.30 | stub-boundary/valid/gzip/20000 | `raw82.76211.json` | `Extract_low_random->extractFile` | 13/18 | 10/14 | 11/19 |
| 8.2.30 | stub-boundary/valid/bzip/20000 | `raw82.76227.json` | `Extract_low_random->extractFile` | 14/18 | 11/14 | 12/19 |
| 8.2.30 | stub-boundary/checksum/plain/1 | `raw82.76244.json` | `Extract_low_random->extractFile` | 11/18 | 9/14 | 9/19 |
| 8.2.30 | stub-boundary/size/plain/1 | `raw82.76258.json` | `Extract_low_random->extractFile` | 11/18 | 8/14 | 8/19 |
| 8.2.30 | stub-boundary/temporary-directory/none/none | `raw82.76280.json` | `Extract_low_random->tmpdir` | 4/9 | 4/14 | 3/19 |
| 8.2.30 | stub-boundary/cleanup/empty/none | `raw82.76289.json` | `Extract_low_random->_removeTmpFiles` | 6/10 | 3/12 | 2/18 |
| 8.2.30 | stub-boundary/cleanup/files/none | `raw82.76309.json` | `Extract_low_random->_removeTmpFiles` | 9/10 | 9/12 | 9/18 |
| 8.2.30 | stub-boundary/cleanup/empty-directory/none | `raw82.76329.json` | `Extract_low_random->_removeTmpFiles` | 9/10 | 9/12 | 9/18 |
| 8.2.30 | stub-boundary/cleanup/populated-directory/none | `raw82.76347.json` | `Extract_low_random->_removeTmpFiles` | 4/10 | 5/12 | 4/18 |
| 8.2.30 | stub-boundary/cleanup/dotfile/none | `raw82.76372.json` | `Extract_low_random->_removeTmpFiles` | 3/10 | 3/12 | 2/18 |
| 8.2.30 | stub-boundary/cleanup/dangling-link/none | `raw82.76385.json` | `Extract_low_random->_removeTmpFiles` | 4/10 | 5/12 | 5/18 |
