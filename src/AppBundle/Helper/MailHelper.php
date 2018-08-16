<?php
/**
 * Created by PhpStorm.
 * User: intern
 * Date: 16.08.2018
 * Time: 09:25
 */

namespace AppBundle\Helper;

/**
 * Class MailHelper
 * @package AppBundle\Helper
 */
class MailHelper implements MailInterface
{
    private $mailer;
    private $templating;
    const FROM = 'pitech.hotel.management@gmail.com';

    /**
     * MailHelper constructor.
     *
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $templating
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * @param string $to
     * @param string $subject
     * @param array  $message
     * @param string $template
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendEmail(string $to, string $subject, array $message, string $template)
    {
        $message = (new \Swift_Message($subject))
            ->setFrom(self::FROM)
            ->setTo($to)
            ->setBody(
                $this->templating->render($template, $message),
                'text/html'
            );
        $this->mailer->send($message);
    }
}
