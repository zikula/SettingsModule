<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace MailerModule;

class MailerModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Mailer Module');
        $meta['description']    = $this->__('Mailer module, provides mail API and mail setting administration.');
        //! module name that appears in URL
        $meta['url']            = $this->__('mailer');
        $meta['version']        = '1.3.2';
        $meta['core_min']       = '1.3.6';

        $meta['securityschema'] = array('ZikulaMailerModule::' => '::');

        return $meta;
    }
}