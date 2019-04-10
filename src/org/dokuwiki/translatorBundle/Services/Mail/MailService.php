<?php
namespace org\dokuwiki\translatorBundle\Services\Mail;

use Monolog\Logger;
use Twig\Environment;

class MailService {

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $template;

    /**
     * @var String
     */
    private $from;

    /**
     * @var Logger
     */
    private $logger;

    private $lastMessage;

    function __construct(Swift_Mailer $mailer, Environment $twig, $from, Logger $logger) {
        $this->mailer = $mailer;
        $this->template = $twig;
        $this->from = $from;
        $this->logger = $logger;
    }

    public function sendEmail($to, $subject, $template, $data = array()) {
        if ($to === '') return;
        $message = $this->createMessage($to, $subject, $template, $data);

        $this->send($message);
    }

    public function sendPatchEmail($to, $subject, $patch, $template, $data = array()) {
        $message = $this->createMessage($to, $subject, $template, $data);

        $attachment = \Swift_Attachment::newInstance($patch, 'language.patch', 'text/plain');
        $message->attach($attachment);

        $this->send($message);
    }

    private function send(\Swift_Message $message) {
        $this->logMail($message);
        $this->mailer->send($message);
    }

    private function createMessage($to, $subject, $template, $data = array()) {
        $message = \Swift_Message::newInstance();
        $message->setTo($to);
        $message->setSubject($subject);
        $message->setFrom($this->from);
        $message->setBody($this->template->render($template, $data));
        $this->lastMessage = $message;
        return $message;
    }

    private function logMail(\Swift_Message $message) {

        $context = array();
        $context['to'] = $message->getTo();
        $context['subject'] = $message->getSubject();
        $context['text'] = $message->getBody();

        $this->logger->debug(sprintf('Sending mail'), $context);

    }

    /**
     * @return string
     */
    public function getLastMessage() {
        return $this->lastMessage;
    }



}
