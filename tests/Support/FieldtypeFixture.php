<?php

/**
 * Supply the EE_Fieldtype members consumed by the archived base fieldtype.
 *
 * Reference: EE_Fieldtype.php public settings and protected content_id getter.
 */
abstract class EE_Fieldtype
{
    public $settings = array();
    protected $content_id = null;

    /**
     * Match EE's content-ID getter, including null before an entry is saved.
     *
     * @return int|null
     */
    public function content_id()
    {
        return $this->content_id;
    }
}
