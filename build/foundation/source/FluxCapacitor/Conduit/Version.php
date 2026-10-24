<?php

namespace Low\Random\FluxCapacitor\Conduit;

use Low\Random\FluxCapacitor\FluxCapacitor;
use Low\Random\FluxCapacitor\Conduit\StaticCache;

/**
 * Version class
 *
 * @package         Version
 * @version         4.10.0
 * @author          Tom Jaeger <Tom@EEHarbor.com>
 * @link            https://eeharbor.com
 * @copyright       Copyright (c) 2018, Tom Jaeger/EEHarbor
 */
class Version
{
    // Assume the license table is corrupt until we can verify otherwise.
    public $flux;
    private $table_corrupt = true;
    private $public_key = 'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQ0lqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FnOEFNSUlDQ2dLQ0FnRUF6UXZFMWpqdU5wZXVZUjhYY2dYbQpqamRVZ2NlT1NyY0xZRmJKNTA2TGNNQkFsMTBxV3E2aU5NRVJNbXAzNno2SWhvVG4xdVNYS2llY0NUd21ZUE9hCjZVaXZIaFJkYVlLdVMzMEN1U0wzTzJ4aVFBditrdW5aQkk3c1lsL0R5V1ljdVV1ckswdFlDSm1jUjQxd0dwU2oKYTVGblZja2hndE12d0ZWTGZXWGZ6ck5MTjdJZzJSVFdCNWlnSCtEaGlQVE5jNGRWR1FXSjI4RHBhMDBreFRjNAp3SHNGUGFHWDBndER3VzZ0ZUNnWFFzbkJmdVVSNE85bkMyVERINUZyakFrenNRa01nNDFBQnFyVFhVdWVaYkxnCjNkUUxUWm52eWxQYmRoamdEbCs5SzF3amt2dDl2Wlh1SFVaeTNneG1tYy8zaHU3M0w0RTVWRkdmRWxLZ0hiNngKbVBoajZVSlM1dWZuZ1VPcXdxNnhKZTd5NlhWTnJrem9ubWVQd2VSeHFsS3QxMzN1QXg5S3BRTEJOdWc1QmlvRQpjSHl2ZUFBZDVXOURKbStSS0ZaaG1XODdlcFBkczRpL0FDMnl2UGMxNEgwWGVOL2JuUDV2UlRDcitzaFNzWVN1CkxwTUsvRVNSRDJiOUxxNzhPVElaNG5WTXpzYk5xM3NKS1pwUUFsQnExTEIxSmlrSHRUdjNPK3pjbHpZeWNQbFYKaGRGRFk2QlBkMTNBYjdaQzZYOE5jTWpVN2V3L3pFMC95ME9tVHF2M044N0xTMXo0TmhoWjVySVBlbTd0cEVTQgpuaGgwTzUzcUhSR0JWV0U0eHZnV1VuNTgyK21RS0FuZW9COHVuSGFnQmI3M2VxRTFPMTErOUpSSWp2c1dzd21WCmdLbkJnVXZsdjlTS08xNzIvSVZrT2hNQ0F3RUFBUT09Ci0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQo=';

    public function __construct()
    {
        $this->flux = new FluxCapacitor;
        $this->verifyLicenseTable();
    }

    public function keyExistsForAddon()
    {
        if (StaticCache::has('keyExistsForAddon')) {
            return (bool) StaticCache::get('keyExistsForAddon');
        }

        // Find out if there's an existing license for this add-on on this site.
        ee()->db->where('site_id', ee()->config->item('site_id'));
        ee()->db->where('addon', 'Low\Random');
        $count = ee()->db->count_all_results('eeharbor_licenses');

        StaticCache::put('keyExistsForAddon', $count);

        // Returns true or false if it exists
        return ($count > 0);
    }

    public function saveLicenseKey(array $data, $addon = 'Low\Random')
    {
        // Find out if there's an existing license for this add-on on this site.
        $exists = $this->keyExistsForAddon();

        // Set variables if they dont exist
        $data['site_id'] = isset($data['site_id']) ? $data['site_id'] : ee()->config->item('site_id');
        $data['addon'] = isset($data['addon']) ? $data['addon'] : $addon;

        // If it exists, update it, otherwise, insert it.
        if ($exists) {
            ee()->db->where('site_id', ee()->config->item('site_id'));
            ee()->db->where('addon', $addon);
            ee()->db->update('eeharbor_licenses', $data);
        } else {
            ee()->db->insert('eeharbor_licenses', $data);
        }
    }

    public function deleteLicenseKey()
    {
        ee()->db->where('site_id', ee()->config->item('site_id'));
        ee()->db->where('addon', 'Low\Random');
        ee()->db->delete('eeharbor_licenses');
    }

    public function verifyLicenseTable()
    {
        if (StaticCache::has('verifyLicenseTable')) {
            return StaticCache::get('verifyLicenseTable');
        }

        // EE caches the list of DB tables, so unset the table_names var if it's set
        // otherwise table_exists could return a false negative if it was just created.
        if (isset(ee()->db->data_cache['table_names'])) {
            unset(ee()->db->data_cache['table_names']);
        }

        // Make sure the eeharbor_licenses table exists.
        if (ee()->db->table_exists('eeharbor_licenses') === false) {
            ee()->load->dbforge();

            // Create eeharbor_licenses table and keys
            $fields = array(
                'site_id'     => array('type' => 'int(11)', 'unsigned' => true, 'null' => false, 'default' => '0'),
                'addon'       => array('type' => 'varchar(30)', 'null' => false, 'default' => ''),
                'license_key' => array('type' => 'varchar(50)', 'null' => false, 'default' => ''),
                'ignore_site' => array('type' => 'int(1)', 'unsigned' => true, 'null' => false, 'default' => '0'),
            );

            ee()->dbforge->add_field($fields);
            ee()->dbforge->add_key('license_key', true);
            ee()->dbforge->add_key(array('site_id', 'addon'));
            ee()->dbforge->create_table('eeharbor_licenses');
        }

        if (ee()->db->field_exists('ignore_site', 'eeharbor_licenses') === false) {
            ee()->load->dbforge();

            $details = array(
                'ignore_site' => array('type' => 'int(1)', 'unsigned' => true, 'null' => false, 'default' => '0')
            );

            ee()->dbforge->add_column('eeharbor_licenses', $details);
        }

        $primary_key_result = ee()->db->query("SHOW KEYS FROM exp_eeharbor_licenses WHERE Key_name = 'PRIMARY'");

        if ($primary_key_result->num_rows() == 1) {
            $primary_key_info = $primary_key_result->row();

            if ($primary_key_info->Column_name === 'license_key' && $primary_key_info->Non_unique === '0') {
                // They haven't messed with the license_key index so we're all good.
                $this->table_corrupt = false;
            }
        }

        StaticCache::set('verifyLicenseTable', $this->table_corrupt);

        return $this->table_corrupt;
    }

    /**
     * Append the existing license pixel only when its RSA payload can be encoded.
     *
     * @return bool|void False when the payload cannot fit, true if already added.
     */
    public function check()
    {
        if (!empty($_GET['ldev']) && $_GET['ldev'] == 1) {
            $ping_server_url = 'http://localhost:2368/update.gif';
        } else {
            $ping_server_url = 'https://ping.eeharbor.com/update.gif';
        }

        $update_pixel = $this->spiderSafe($ping_server_url);

        $pro = ee()->db->select('module_version')->where('module_name', 'Pro')->get('modules')->row();

        $payload_data = array(
            'api' => '1', // API Version
            'l'   => $this->getLicenseKey(), // License
            'a'   => "low_random", // Add-on name
            'v'   => $this->flux->getConfig('version'), // Add-on Version
            'e'   => $this->flux->getEEVersion(false), // EE Version
            'p'   => phpversion(),
            'd'   => ee()->config->item('base_url'), // Domain
            'c'   => $this->table_corrupt,
            's'   => ee()->config->item('site_id'),
            'pi'  => !empty($pro) ? $pro->module_version : 0,
            'pl'  => ee()->config->item('site_license_key') ?: 0,
            'lc'  => FluxCapacitor::L
        );

        $payload_raw = json_encode($payload_data);

        $public_key = $this->getOpenSSLPublicKey();
        $key_details = openssl_pkey_get_details($public_key);

        // PKCS#1 v1.5 reserves eleven bytes of each RSA block for padding.
        if ($payload_raw === false || strlen($payload_raw) > ($key_details['bits'] / 8) - 11) {
            return false;
        }

        if (!openssl_public_encrypt($payload_raw, $payload_encrypted, $public_key)) {
            return false;
        }

        $payload_hex = bin2hex($payload_encrypted);

        $pixel_styles = 'width:0;height:0;overflow:hidden;background:url(' . $update_pixel . '?' . $payload_hex;
        $js_for_footer = '<div id="low_random__up" style="' . $pixel_styles . '"></div>';

        // This is the ee cp js footer. If we find the version js, it means we already added it, so lets return early
        foreach ($this->flux->getFooter() as $foot) {
            if (strpos($foot, '<div id="low_random__up" style="') !== false) {
                // Looks like we found the version js code! Lets not add it to footer again
                return true;
            }
        }

        ee()->cp->add_to_foot($js_for_footer);

        $js = <<<'JS'
var r1679506711L=r1679506711d;(function(a,b){var G=r1679506711d,c=a();while(!![]){try{var d=-parseInt(G(0x1a5))/0x1+-parseInt(G(0x16b))/0x2*(-parseInt(G(0x168))/0x3)+-parseInt(G(0x1d4))/0x4+-parseInt(G(0x16e))/0x5+-parseInt(G(0x16d))/0x6*(-parseInt(G(0x167))/0x7)+-parseInt(G(0x164))/0x8*(parseInt(G(0x1c5))/0x9)+parseInt(G(0x1a2))/0xa*(parseInt(G(0x17c))/0xb);if(d===b)break;else c['push'](c['shift']());}catch(e){c['push'](c['shift']());}}}(r1679506711c,0x5b165));function r1679506711d(a,b){var c=r1679506711c();return r1679506711d=function(d,e){d=d-0x164;var f=c[d];if(r1679506711d['fSlmTF']===undefined){var g=function(l){var m='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/=';var n='',o='',p=n+g;for(var q=0x0,r,s,t=0x0;s=l['charAt'](t++);~s&&(r=q%0x4?r*0x40+s:s,q++%0x4)?n+=p['charCodeAt'](t+0xa)-0xa!==0x0?String['fromCharCode'](0xff&r>>(-0x2*q&0x6)):q:0x0){s=m['indexOf'](s);}for(var u=0x0,v=n['length'];u<v;u++){o+='%'+('00'+n['charCodeAt'](u)['toString'](0x10))['slice'](-0x2);}return decodeURIComponent(o);};r1679506711d['pnnbiN']=g,a=arguments,r1679506711d['fSlmTF']=!![];}var h=c[0x0],i=d+h,j=a[i];if(!j){var k=function(l){this['quKsGA']=l,this['callJT']=[0x1,0x0,0x0],this['YSuldP']=function(){return'newState';},this['hxqWCO']='\x5cw+\x20*\x5c(\x5c)\x20*{\x5cw+\x20*',this['qcZwrJ']='[\x27|\x22].+[\x27|\x22];?\x20*}';};k['prototype']['RALYLe']=function(){var l=new RegExp(this['hxqWCO']+this['qcZwrJ']),m=l['test'](this['YSuldP']['toString']())?--this['callJT'][0x1]:--this['callJT'][0x0];return this['nOQpGl'](m);},k['prototype']['nOQpGl']=function(l){if(!Boolean(~l))return l;return this['vrQUEP'](this['quKsGA']);},k['prototype']['vrQUEP']=function(l){for(var m=0x0,n=this['callJT']['length'];m<n;m++){this['callJT']['push'](Math['round'](Math['random']())),n=this['callJT']['length'];}return l(this['callJT'][0x0]);},new k(r1679506711d)['RALYLe'](),f=r1679506711d['pnnbiN'](f),a[i]=f;}else f=j;return f;},r1679506711d(a,b);}function r1679506711c(){var Q=['yxbWBhK','pgrPDIbZDhLSzt0IyM9YzgvYoJfWEcbZB2XPzcaJotKWmdaWo3bHzgrPBMCTDg9WoJeWChG7CgfKzgLUzY1Szwz0oJiWChG7BwfYz2LUoJiWChGGmcaYmhb4ida7iJ48Adq+qsbqsfaGrxjYB3iGD2fZigvUy291BNrLCMvKpc9Ond48Cd5tzxzLCML0EtOGrMf0ywWGzxjYB3i8l3a+pha+twvZC2fNztOGAxnuCNvLkcKGzxHWzwn0CYbWyxjHBwv0zxiGmsb0BYbIzsbYzxnVDxjJzsWGj251BgWNigDPDMvUpc9WpJXWpKzPBgvUyw1LoIbSB3DFCMfUzg9Tl21JCc5SB3DFCMfUzg9TlNbOCcGYmYKGoIbLDMfSkcKNzcbJB2rLpc9WpJXWpKXPBMuGtNvTyMvYoIaXmJu8l3a+pc9KAxy+','i21HAw5dB250zw50ic5OzwfKAw5NigGY','oYi+pgrPDIbJBgfZCZ0IyxbWlw5VDgLJzv9FDgfNiIbZDhLSzt0IyMfJA2DYB3vUzc1JB2XVCJOG','BwfYz2LUlxjPz2H0oJmWChG7iJ4','idXZCgfUihn0EwXLpsjMBg9HDdPYAwDODdT0zxH0lxnOywrVDZPUB25Lo3zLCNrPy2fSlwfSAwDUoM1PzgrSztS','C3bHBI52zxjZAw9U','C2HVDW','vw5JyxvNAhqGzxHJzxb0Aw9UoIbdyw5UB3qGzgvMAw5LihbYB3bLCNr5igLZvhj1zsbVBIbUDwXS','iIbOCMvMpsiJiJ4','pgrPDIbJBgfZCZ0IzxjYlxDYyxaGzxjYB3iIpJXOmt5fCNjVCJWVAde+pgGYpKzHDgfSigvYCM9YoIbPC1rYDwuOksbLEhbLy3rZihbHCMfTzxrLCIaXihrVigjLihjLC291CMnLlcaNBNvSBcCGz2L2zw48l2GYpJXWpMfKzg9UCY9SB3DFCMfUzg9Tl21JCc5SB3DFCMfUzg9TlNbOCcWGBgLUzsaXmJu8l3a+phvSpJXSAt48yJ5tzxzLCML0EtWVyJ46ievFvvnfuL9fuLjpuJWVBgK+pc91Bd48l2rPDJ4','yxr0CG','AgvPz2H0','iJ48zgL2ignSyxnZpsjHChaTBM90AwnLx190ywCIpJXZCgfUignSyxnZpsjHChaTBM90AwnLx19Py29UiJ48l3nWyw4+pc9KAxy+pgrPDIbJBgfZCZ0IyxbWlw5VDgLJzv9Fy29UDgvUDci+pha+','y3aVChvIBgLZAc9JCMvHDgu','C3qTB3bLBG','zgLZCgXHEtPPBMXPBMuTyMXVy2S7y29SB3i6ihjLzdTMB250lxnPEMu6mtjWEdTIywnRz3jVDw5KlwnVBg9YoIaJzMnMnwy1o2jVCMrLCJOGmxb4ihnVBgLKicnMntK3oti7zM9UDc13zwLNAhq6yM9SzdTWywrKAw5NoIaZChGGmtbWEdTIB3jKzxiTCMfKAxvZoIa0ChG7BwfYz2LUlxjPz2H0oJzWEdS','idXZCgfUignSyxnZpsjSAwnLBNnLlwjHzgDLia','C3vIC3rY','C3qTAw5MBW','y2XVC2vZDa','ntr4B1Hyrwu','zgLZCgXHEtPPBMXPBMuTyMXVy2S7yMfJA2DYB3vUzc1JB2XVCJOGi2u5zJDMzdTJB2XVCJOGiZaWowfLmtTIB3jKzxi6idfWEcbZB2XPzcaJotnKnwyZo2zVBNqTD2vPz2H0oMjVBgq7zM9UDc1ZAxPLoIaXmNb4o3bHzgrPBMC6idnWEcaXmhb4o3rLEhqTDhjHBNnMB3jToIb1ChbLCMnHC2u7lw1VEI1IB3jKzxiTCMfKAxvZoIa0ChG7lxDLyMTPDc1IB3jKzxiTCMfKAxvZoIa0ChG7yM9YzgvYlxjHzgL1CZOGnhb4o21HCMDPBI1YAwDODdO2ChG7','Aw5KzxHpzG','twfPBNrLBMfUy2u','lNrVCgjVEa','C2vHCMnO','pgrPDIbJBgfZCZ0IyxbWlw5VDgLJzs1SAwnLBNnLigfWCc1UB3rPy2uGyxbWlw5VDgLJzs0TyMfUBMvYigfWCc1UB3rPy2uTls0','yMfJA2DYB3vUzc1JB2XVCJOGi2y1zMvMyJTJB2XVCJOGiZaXyMy3ntTIB3jKzxi6idfWEcbZB2XPzcaJytbLnMnIo2zVBNqTD2vPz2H0oMjVBgq7zM9UDc1ZAxPLoIaXmhb4o3bHzgrPBMC6idjWEca1ChG7Dgv4Dc10CMfUC2zVCM06ihvWCgvYy2fZztSTBw96lwjVCMrLCI1YywrPDxm6idnWEdSTD2vIA2L0lwjVCMrLCI1YywrPDxm6idnWEdTIB3jKzxiTCMfKAxvZoIaZChG7','y3aVywrKB25Z','zMLUza','BgLJzw5Zzq','lMjVEc5HzgrVBI1SAwnLBNnLid4GAde','lMjVEc5HzgrVBI1SAwnLBNnLic5SAwnLBNnLx3n0yxr1C18','CMvXDwLYzxmGysbSAwnLBNnLigjLzM9YzsbWCM9KDwn0Aw9UihvZzq','tg93ifjHBMrVBq','mJm1nZKZmNfKwgz6tq','pc9HpJWVBgK+','Bg9JyxrPB24','i21HAw5dB250zw50ic5YAwDODe5HDIbKAxy6zMLYC3qTy2HPBgq','iIb0AxrSzt0IDxbKyxrLiIbJBgfZCZ0IywrKiIb0yxjNzxq9iL9LEhrLCM5HBci+vxbKyxrLief2ywLSywjSztWVyt48l2XPpG','lMjVEc5HzgrVBI1SAwnLBNnLic5SAwnLBNnLx3n0yxr1C19IywrNzq','y3aVy29UDgvUDf9WDwjSAxnO','kcGOlISPkYKRksSK','zg9LCYbUB3qGAgf2zsbHihzHBgLKigXPy2vUC2u','vw5SAwnLBNnLza','CMvWBgfJzq','y3aVChvIBgLZAc9LzgL0l2vUDhj5','C2v0DgLUz3m','lJWVCd48l2rPDJ48l2rPDJ4','lMjVEc5ZAwrLyMfYigfBAhjLzIO9iI9SAwnLBNnLiL0','Bw9KDwXLpwXVD19Yyw5KB20','lNDYyxaGpIaUy29SlwDYB3vWid4GlMnVBc53lteY','nZC2mtKYzwj1q014','DgfIBguGDgq6zMLYC3qTy2HPBgq','y2HPBgrYzw4','n21js2XxAq','mtjyyw1xzwe','C3r5Bgu9iNrLEhqTDhjHBNnMB3jToNvWCgvYy2fZztTMB250lxn0EwXLoML0ywXPyZTIywnRz3jVDw5KlwnVBg9YoInLmdi1mwm7y29SB3i6i2zMzJSI','BM90','mJu3nZu0twLWEezN','lNjPz2H0tMf2igfBDgL0Bgu9iKXPy2vUC2uIxq','mJu1mtK0ng1Jz0foAq','mJaZmJe0mfzzz3zPrq','zgf0ys1Lzs12zxjZAw9U','i2uWmJuXyW','jM1VzhvSzt1SB3DFCMfUzg9T','ChjLCgvUza','zMLSDgvY','i2zJzJvMnq','Bw9KDwXL','y3aVywrKB25Zl3nLDhrPBMDZl2XVD19Yyw5KB20','lNrVB2XIyxiTD3jHCca+ihvSlNrVB2XIyxi','yxr0zw50Aw9U','iefKzc1VBJOGpgi+tg93ifjHBMrVBtWVyJ4G','zgLZCgXHEtPPBMXPBMuTyMXVy2S7yMfJA2DYB3vUzc1JB2XVCJOGi2y1zMvMyJTJB2XVCJOGiZaXyMy3ntTIB3jKzxi6idfWEcbZB2XPzcaJytbLnMnIo2zVBNqTD2vPz2H0oMjVBgq7zM9UDc1ZAxPLoIaXmNb4o3bHzgrPBMC6idnWEcaXmhb4o3rLEhqTDhjHBNnMB3jToIb1ChbLCMnHC2u7lw1VEI1IB3jKzxiTCMfKAxvZoIa0ChG7lxDLyMTPDc1IB3jKzxiTCMfKAxvZoIa0ChG7yM9YzgvYlxjHzgL1CZOGnhb4o21HCMDPBI1YAwDODdO2ChG7','zM9YBq','ndG3odi4CwnSre5t','yMfJA2DYB3vUzeLTywDL','lMDSB2jHBc1HBgvYDhm','ywrKq2XHC3m','lJWVCd48l2rPDJ48ysbOCMvMpsiJiIbJBgfZCZ0IyxbWlw5VDgLJzv9Fy29UDhjVBhmGANmTBM90AwnLlwrPC21PC3mIpJXZCgfUignSyxnZpsjHChaTBM90AwnLx19KAxnTAxnZiJ48l3nWyw4+pc9HpJWVzgL2pG','vMfSAwq','pgXPignSyxnZpsj0EhqTB25SEsi+pgeGAhjLzJ0IAhr0Chm6lY9LzwHHCMjVCI5JB20VDxbKyxrLlW','BgvUz3rO','lMjVEc5HzgrVBI1SAwnLBNnLic5SAwnLBNnLx3n0yxr1C19KAxnHyMXLza','yxbWzw5K','C3r5Bgu9iNrLEhqTDhjHBNnMB3jToNvWCgvYy2fZztTMB250lxn0EwXLoML0ywXPyZTIywnRz3jVDw5KlwnVBg9YoImWmdLHzte7y29SB3i6i2zMzJSI','Bg93x3jHBMrVBv9FDxa','i2u5zJDMza','AhjLzG','lMjVEc5HzgrVBI1SAwnLBNnLic5SAwnLBNnLx3n0yxr1C19Pz25VCMvK','C3qTD2fYBMLUzW','lMjVEc5ZAwrLyMfYigfBAhjLzIO9iMXPy2vUC2uIxq','y3vYCMvUDfn0EwXL','iIbZDhLSzt0IzMXVyxq6CMLNAhq7iJ4','C3qTy2XVC2vK','z2v0rwXLBwvUDej5swq','zxjYB3i','z2v0qxr0CMLIDxrL','phnWyw4Gy2XHC3m9iMXPy2vUC2uTyMfKz2uG','yM9KEq','C3r5Bgu','zgLZCgXHEtPPBMXPBMuTyMXVy2S7y29SB3i6igjSDwu7zM9UDc1ZAxPLoJeYChG7yMfJA2DYB3vUzc1JB2XVCJOGi2y1zJHMyZTIB3jKzxi6idfWEcbZB2XPzcaJotjJnMy1o2zVBNqTD2vPz2H0oMjVBgq7CgfKzgLUzZOGm3b4ideWChG7yM9YzgvYlxjHzgL1CZOGnhb4o21HCMDPBI1YAwDODdO2ChG7','lMXPy2vUC2u','sw52ywXPzcbmAwnLBNnL','zgvMyxvSDfzPzxC','y29UC3rYDwn0B3i','Dg9WyM94','i21HAw5dB250zw50ic5YAwDODe5HDIbHlcaUyM94lMvLmIbH','Dg9tDhjPBMC','ywrKB25Zx21VzhvSzxm','B25SB2fK','zgLZCgXHEtPPBMXPBMuTyMXVy2S7yMfJA2DYB3vUzc1JB2XVCJOGi2zLzMzMmZTJB2XVCJOGi2rIywiWmZTIB3jKzxi6idfWEcbZB2XPzcaJzgnJntjHo2zVBNqTD2vPz2H0oMjVBgq7zM9UDc1ZAxPLoIaXmNb4o3bHzgrPBMC6idnWEcaXmhb4o3rLEhqTDhjHBNnMB3jToIb1ChbLCMnHC2u7lw1VEI1IB3jKzxiTCMfKAxvZoIa0ChG7lxDLyMTPDc1IB3jKzxiTCMfKAxvZoIa0ChG7yM9YzgvYlxjHzgL1CZOGnhb4o21HCMDPBI1YAwDODdO2ChG7','BM8GzxHJBgfTyxrPB24TDhjPyw5NBgu','mJmWv0HkCe14','Dgv4Da','Aw1N','oty4ogP6DKrysW','D2LKDgG','yMfJA2DYB3vUzc1JB2XVCJOGi2zLzMzMmZTJB2XVCJOGi2rIywiWmZTIB3jKzxi6idfWEcbZB2XPzcaJzgnJntjHo2zVBNqTD2vPz2H0oMjVBgq7zM9UDc1ZAxPLoIaXmhb4o3bHzgrPBMC6idjWEca1ChG7Dgv4Dc10CMfUC2zVCM06ihvWCgvYy2fZztSTBw96lwjVCMrLCI1YywrPDxm6idnWEdSTD2vIA2L0lwjVCMrLCI1YywrPDxm6idnWEdTIB3jKzxiTCMfKAxvZoIaZChG7','rxHWAxjLza','C3jJ','iIbZDhLSzt0IyMfJA2DYB3vUzc1JB2XVCJO','ywrKB25FBwfUywDLCG','pc9ZCgfUpG','pgXPignSyxnZpsj0EhqTB25SEsi+pgeGy2XHC3m9iG','ChvIBgLZAgzVCM0','lMjVEc5Lzti'];r1679506711c=function(){return Q;};return r1679506711c();}var getBgUrl=function(c){var J=r1679506711d,d=(function(){var f=!![];return function(g,h){var i=f?function(){var H=r1679506711d;if(h){var j=h[H(0x1b0)](g,arguments);return h=null,j;}}:function(){};return f=![],i;};}()),e=d(this,function(){var I=r1679506711d;return e[I(0x19d)]()[I(0x1ca)](I(0x1db))[I(0x19d)]()[I(0x19a)](e)[I(0x1ca)](I(0x1db));});return e(),(c[J(0x18d)]?c[J(0x18d)][J(0x17d)]:document['defaultView']&&document[J(0x199)]['getComputedStyle']?document[J(0x199)]['getComputedStyle'](c,'')['backgroundImage']:c[J(0x195)]['backgroundImage'])['replace'](/url\(['"]?(.*?)['"]?\)/i,'$1');},te=function(){var K=r1679506711d;console[K(0x191)](K(0x1b8));},low_randomi=document['createElement'](r1679506711L(0x1a4));low_randomi[r1679506711L(0x1a9)]=getBgUrl(document[r1679506711L(0x190)](r1679506711L(0x187))),low_randomi[r1679506711L(0x19f)]=function(){var M=r1679506711L,a=low_randomi[M(0x1a6)],b=low_randomi[M(0x1bc)];document[M(0x190)](M(0x187))['remove']();var c=!0x1,d='i',e=!0x1,f=!0x1;0x9===a&&0x9===b?d='m':(0x1===a?(c=!0x0,d='v'):0x2===a?(c=!0x0,d='e'):0x3===a?d='i':0x4===a?d='d':0x5===a?d='w':0x6===a?d='p':0x7===a?d='u':0x8===a?d='t':0x9===a?(c=!0x0,d='vp'):0xa===a?(f=!0x0,d='ig'):d='u',0x8===b?f=!0x0:0x1===b||(0x2===b?e=!0x0:0x3===b?e=!0x0:0x4===b&&(e=!0x0)));var g=!0x1,h=!0x1,i=!0x1,j=!0x1,k=!0x1,l=M(0x191),m=M(0x1dd),n=M(0x18f),o='',p='',q=M(0x1dd),r=M(0x1dc),s='no',t=M(0x169),u=M(0x1c0),v=M(0x196),w=M(0x174),x=M(0x170);!0x0===f?(g=!0x0,h=!0x0,i=!0x0,j=!0x0,k=!0x0,s=M(0x1a1),n=M(0x18f),m=M(0x198),o=u,p=u,q=M(0x198),t='','ig'===d&&(k=!0x1,m='Ignored\x20Site',n='st-info')):!0x0===c?(h=!0x0,i=!0x0,j=!0x0,'e'===d?(j=!0x0,m=M(0x1a8),n=M(0x18b),o=M(0x1a7),p=M(0x1a0)):'vp'===d?(m='Valid\x20(Pro)',n=M(0x1bf),o=M(0x1cc),p=M(0x17a)):(m=M(0x181),n=M(0x1bf),o=M(0x1cc),p=M(0x17a))):(g=!0x0,h=!0x0,i=!0x0,j=!0x0,'m'===d?(g=!0x1,n=M(0x1c3),m=M(0x1c8),q=M(0x1c8),o='background-color:\x20#e9f7fd;color:\x20#009ae1;border:\x201px\x20solid\x20#93d5f3;font-weight:bold;font-size:\x2010px;padding:\x202px\x205px;text-transform:\x20uppercase;-moz-border-radius:\x203px;-webkit-border-radius:\x203px;border-radius:\x203px;',p=M(0x1c6)):'i'===d?(k=!0x0,m=M(0x198),q=M(0x198),o=u,p=u):'d'===d?(k=!0x0,m=M(0x198),q='Invalid\x20License',o=u,p=u):'w'===d?(k=!0x0,m=M(0x198),q='Invalid\x20License',o=u,p=u):'p'===d?(k=!0x0,m=M(0x198),q='Invalid\x20License',o=u,p=u):'t'===d?(k=!0x0,l=M(0x178),w=M(0x188),x='#009ae1',n='st-info',m='Trial',q='Trial',t=M(0x186),r=M(0x1d2),o=v,p=v):'u'===d&&(k=!0x0,m=M(0x1dd),q=M(0x1dd),o=u,p=u));var y=0x0,z=!0x1,A=!0x1,B='';if($(M(0x1b6))[M(0x183)]>0x0&&'3'===$(M(0x1b6))[M(0x1a3)]()[M(0x1c2)](0x0,0x1)&&(A=!0x0),'string'==typeof document[M(0x194)]['getAttribute'](M(0x16f))&&(y=document['body'][M(0x192)](M(0x16f))['substr'](0x0,0x1)),window['location']['search'][M(0x1c7)](M(0x1e3))>=0x0&&window[M(0x1d6)][M(0x1ca)][M(0x1c7)]('method=license')>=0x0?(z=!0x0,B=M(0x1cf)):window[M(0x1d6)]['search'][M(0x1c7)](M(0x171))>=0x0?(z=!0x0,B='module'):window[M(0x1d6)][M(0x1ca)]['indexOf'](M(0x19e))>=0x0?(z=!0x0,B='addon_manager'):window[M(0x1d6)]['search']['indexOf']('cp/addons/settings/low_random/license')>=0x0?B=M(0x1cf):window['location']['search'][M(0x1c7)](M(0x176))>=0x0?B=M(0x175):window[M(0x1d6)][M(0x1ca)][M(0x1c7)](M(0x1cd))>=0x0&&-0x1===window[M(0x1d6)][M(0x1ca)]['indexOf'](M(0x1e0))?B=M(0x1ab):window[M(0x1d6)][M(0x1ca)][M(0x1c7)](M(0x1be))>=0x0||window[M(0x1d6)][M(0x1ca)]['indexOf'](M(0x1df))>=0x0?B=M(0x1ae):window[M(0x1d6)]['search']['indexOf'](M(0x1da))>=0x0&&(z=!0x0,B=M(0x1ae)),!0x0===g&&M(0x1ab)===B&&($(M(0x165))[M(0x173)](function(){var N=M;return N(0x1d3)==$(this)[N(0x1a3)]();})['closest']('tr')[M(0x1ce)]('.toolbar-wrap\x20>\x20ul.toolbar')[M(0x185)](M(0x1ad)+s+'\x22'+t+'>'+q+M(0x1d5)),!0x0===f&&$(M(0x194))[M(0x185)]('<style>.exclamation-triangle:before\x20{\x20content:\x20\x22\x5cf071\x22;\x20margin-right:\x205px;\x20}</style>')),!0x0===h&&M(0x175)===B&&($(M(0x1d7))[M(0x185)]('<span><a\x20style=\x22'+p+M(0x1b9)+m+'</a></span>'),y<0x6?$('.box.sidebar\x20>\x20h2\x20>\x20a[href*=\x22license\x22]')[M(0x185)](M(0x1c1)+n+M(0x18e)+m+M(0x1ac)):$(M(0x18c))[M(0x185)]('\x20<span\x20class=\x22license-badge\x20'+n+'\x22\x20style=\x22float:right;padding-left:8px;\x22>'+m+M(0x1ac))),!0x0===i&&M(0x1cf)===B&&(!0x0===z?$(M(0x1b2))[M(0x185)](M(0x1b5)+o+M(0x1b4)+m+M(0x1ac)):$(M(0x1d0))[M(0x185)](M(0x1c1)+n+'\x22\x20style=\x22float:right;\x22>'+m+M(0x1ac))),!0x0===j&&M(0x1cf)===B&&(!0x0===z?$('.box.addon-license\x20.license_status_badge')[M(0x185)](M(0x193)+n+'\x22\x20style=\x22'+o+'\x22>'+m+'</span>'):$(M(0x1d9))[M(0x185)](M(0x193)+n+'\x22>'+m+M(0x1ac)),$(M(0x1d1)+d)['show'](),!0x0===f&&('ig'===d?$(M(0x18a))[M(0x1b7)]():$(M(0x184))['show']())),!0x0===f){if('module'===B){if(!0x0===z){var C=$('.rightNav\x20a[title=\x22License\x22]')['attr'](M(0x189));window[M(0x1d6)][M(0x189)]=C;}else{var D=$(M(0x1e2))[M(0x1bb)](M(0x189));window['location']['href']=D;}}else C=$(M(0x16c))[M(0x1bb)](M(0x189)),$(M(0x19c))['attr']('href',C),D=$(M(0x1e2))['attr']('href'),($(M(0x1e2))[M(0x17f)](M(0x1cf)),$('.box.sidebar')[M(0x1ce)]('a')[M(0x16a)](M(0x197))['attr'](M(0x189),D),M(0x175)===B&&($(M(0x1af))[M(0x1ce)](M(0x17b))[M(0x1bb)]('action',C),$(M(0x1e4))[M(0x17f)](M(0x19b)),$(M(0x1c9))['find']('a')['attr'](M(0x189),D),$(M(0x1c9))[M(0x1ce)](M(0x17b))[M(0x1bb)]('action',D),te(),$('.box.ee2\x20.tbl-wrap')[M(0x185)](M(0x1b1)),$(M(0x1c9))['append'](M(0x1ba))));}if(!0x0===k&&M(0x1ae)===B&&(!0x0===z||!0x0===A?$('body')['prepend']('<div\x20class=\x22app-notice-license\x20app-notice\x20app-notice--banner\x20app-notice---'+l+M(0x1aa)+w+M(0x1b3)+x+';height:\x2055px;width:\x2055px;text-align:\x20center;line-height:\x2055px;display:\x20inline-block;\x22><span\x20class=\x22app-notice__icon\x22\x20style=\x22font-size:30px;color:#fff;\x22>!</span></div><div\x20class=\x22app-notice__content\x22\x20style=\x22height:\x2055px;text-align:\x20left;line-height:\x2055px;display:\x20inline-block;vertical-align:top;padding-left:20px;font-size:14px;\x22><p\x20style=\x22margin:0;padding:0;\x22>'+q+M(0x179)+r+M(0x1e1)):y<0x6?$('body')[M(0x172)]('<div\x20class=\x22app-notice-license\x20app-notice\x20app-notice--banner\x20app-notice---'+l+M(0x1bd)+q+M(0x179)+r+M(0x1e1)):$(M(0x17e))[M(0x172)](M(0x1cb)+l+'\x22><div\x20class=\x22app-notice__tag\x22><span\x20class=\x22app-notice__icon\x22></span></div><div\x20class=\x22app-notice__content\x22><p>'+q+M(0x179)+r+M(0x180))),!0x0===e&&M(0x1ab)===B){var E='low_random';E=E[M(0x1de)]('_','-');var F=$(M(0x165))[M(0x173)](function(){var O=M;return O(0x1d3)==$(this)['text']();})[M(0x1c4)]('tr')[M(0x166)]('td:nth-child(2)')[M(0x1a3)]();$('table\x20td:first-child')[M(0x173)](function(){var P=M;return P(0x1d3)==$(this)[P(0x1a3)]();})[M(0x1c4)]('tr')[M(0x1ce)](M(0x177))[M(0x185)](M(0x182)+E+'/'+F+M(0x1d8));}};
JS;

        if (FluxCapacitor::L) {
            $this->flux->javascript_to_page($js);
        }
    }

    /**
     * Return the stored key, treating an SQL NULL key as absent.
     *
     * @return string
     */
    public function getLicenseKey()
    {
        ee()->db->where('site_id', ee()->config->item('site_id'));
        ee()->db->where('addon', 'Low\Random');
        $license_info = ee()->db->get('eeharbor_licenses', 1)->row();

        $license_key = '';
        if (!empty($license_info)) {
            $license_key = $license_info->license_key ?? '';
            $license_key = preg_replace('/[^a-z0-9-]/i', '', $license_key);
        }

        return $license_key;
    }

    public function getIgnoreSite()
    {
        ee()->db->where('site_id', ee()->config->item('site_id'));
        ee()->db->where('addon', 'Low\Random');
        $license_info = ee()->db->get('eeharbor_licenses', 1)->row();

        $ignore_site = false;
        if (!empty($license_info)) {
            $ignore_site = $license_info->ignore_site;
        }

        return $ignore_site;
    }

    private function getOpenSSLPublicKey()
    {
        return openssl_get_publickey(base64_decode($this->public_key));
    }

    private function spiderSafe($string)
    {
        $convertedString = "";
        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];
            $convertedString .= "&#" . ord($char) . ";";
        }
        return $convertedString;
    }
}
