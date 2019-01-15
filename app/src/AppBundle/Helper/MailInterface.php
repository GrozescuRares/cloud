<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 10:09
 */

namespace AppBundle\Helper;

/**
 * Interface MailInterface
 * @package AppBundle\Helper
 */
interface MailInterface
{
    /**
     * @param string $to
     * @param string $subject
     * @param array  $message
     * @param string $template
     *
     * @return mixed
     */
    public function sendEmail(string $to, string $subject, array $message, string $template);
}
