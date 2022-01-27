<?php

namespace app\components\swiftmailer;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\swiftmailer
 */
class SwiftMailTransport implements \Swift_Transport
{
    /** Additional parameters to pass to mail() */
    private $_extraParams = '-f%s';
    /** The event dispatcher from the plugin API */
    private $_eventDispatcher;
    /** An invoker that calls the mail() function */
    private $_invoker;

    /**
     * Create a new MailTransport with the $log.
     *
     * @param SwiftMailInvoker $invoker
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     */
    public function __construct(SwiftMailInvoker $invoker, \Swift_Events_EventDispatcher $eventDispatcher)
    {
        $this->_invoker = $invoker;
        $this->_eventDispatcher = $eventDispatcher;
    }

    /**
     * Not used.
     */
    public function isStarted()
    {
        return false;
    }

    /**
     * Not used.
     */
    public function start()
    {
    }

    /**
     * Not used.
     */
    public function stop()
    {
    }

    /**
     * @param $params
     * @return $this
     */
    public function setExtraParams($params)
    {
        $this->_extraParams = $params;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtraParams()
    {
        return $this->_extraParams;
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     * @throws \Swift_TransportException
     */
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $failedRecipients = (array)$failedRecipients;
        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }
        $count = (
            count((array)$message->getTo())
            + count((array)$message->getCc())
            + count((array)$message->getBcc())
        );
        $toHeader = $message->getHeaders()->get('To');
        $subjectHeader = $message->getHeaders()->get('Subject');
        if (0 === $count) {
            $this->_throwException(new \Swift_TransportException('Cannot send message without a recipient'));
        }
        $to = $toHeader ? $toHeader->getFieldBody() : '';
        $subject = $subjectHeader ? $subjectHeader->getFieldBody() : '';
        $reversePath = $this->_getReversePath($message);
        // Remove headers that would otherwise be duplicated
        $message->getHeaders()->remove('To');
        $message->getHeaders()->remove('Subject');
        $messageStr = $message->toString();
        if ($toHeader) {
            $message->getHeaders()->set($toHeader);
        }
        $message->getHeaders()->set($subjectHeader);
        // Separate headers from body
        if (false !== $endHeaders = strpos($messageStr, "\r\n\r\n")) {
            $headers = substr($messageStr, 0, $endHeaders) . "\r\n"; //Keep last EOL
            $body = substr($messageStr, $endHeaders + 4);
        } else {
            $headers = $messageStr . "\r\n";
            $body = '';
        }
        unset($messageStr);
        if ("\r\n" != PHP_EOL) {
            // Non-windows (not using SMTP)
            $headers = str_replace("\r\n", PHP_EOL, $headers);
            $subject = str_replace("\r\n", PHP_EOL, $subject);
            $body = str_replace("\r\n", PHP_EOL, $body);
            $to = str_replace("\r\n", PHP_EOL, $to);
        } else {
            // Windows, using SMTP
            $headers = str_replace("\r\n.", "\r\n..", $headers);
            $subject = str_replace("\r\n.", "\r\n..", $subject);
            $body = str_replace("\r\n.", "\r\n..", $body);
            $to = str_replace("\r\n.", "\r\n..", $to);
        }
        if ($this->_invoker->mail($to, $subject, $body, $headers, $this->_formatExtraParams($this->_extraParams, $reversePath))) {
            if ($evt) {
                $evt->setResult(\Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }
        } else {
            $failedRecipients = array_merge(
                $failedRecipients,
                array_keys((array)$message->getTo()),
                array_keys((array)$message->getCc()),
                array_keys((array)$message->getBcc())
            );
            if ($evt) {
                $evt->setResult(\Swift_Events_SendEvent::RESULT_FAILED);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }
            $message->generateId();
            $count = 0;
        }
        return $count;
    }

    /**
     * Register a plugin.
     *
     * @param \Swift_Events_EventListener $plugin
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }

    /**
     * Throw a TransportException, first sending it to any listeners
     *
     * @param \Swift_TransportException $e
     * @throws \Swift_TransportException
     */
    protected function _throwException(\Swift_TransportException $e)
    {
        if ($evt = $this->_eventDispatcher->createTransportExceptionEvent($this, $e)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'exceptionThrown');
            if (!$evt->bubbleCancelled()) {
                throw $e;
            }
        } else {
            throw $e;
        }
    }

    /**
     * Determine the best-use reverse path for this message
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @return mixed|null|string
     */
    private function _getReversePath(\Swift_Mime_SimpleMessage $message)
    {
        $return = $message->getReturnPath();
        $sender = $message->getSender();
        $from = $message->getFrom();
        $path = null;
        if (!empty($return)) {
            $path = $return;
        } elseif (!empty($sender)) {
            $keys = array_keys($sender);
            $path = array_shift($keys);
        } elseif (!empty($from)) {
            $keys = array_keys($from);
            $path = array_shift($keys);
        }
        return $path;
    }

    /**
     * Fix CVE-2016-10074 by disallowing potentially unsafe shell characters.
     *
     * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
     *
     * @param $string
     * @return bool
     */
    private function _isShellSafe($string)
    {
        // Future-proof
        if (@escapeshellcmd($string) !== $string || !in_array(@escapeshellarg($string), array("'$string'", "\"$string\""))) {
            return false;
        }
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];
            // All other characters have a special meaning in at least one common shell, including = and +.
            // Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
            // Note that this does permit non-Latin alphanumeric characters based on the current locale.
            if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return php mail extra params to use for invoker->mail.
     *
     * @param $extraParams
     * @param $reversePath
     *
     * @return string|null
     */
    private function _formatExtraParams($extraParams, $reversePath)
    {
        if (false !== strpos($extraParams, '-f%s')) {
            if (empty($reversePath) || false === $this->_isShellSafe($reversePath)) {
                $extraParams = str_replace('-f%s', '', $extraParams);
            } else {
                $extraParams = sprintf($extraParams, $reversePath);
            }
        }
        return !empty($extraParams) ? $extraParams : null;
    }

    /**
     * Check if this Transport mechanism is alive.
     *
     * If a Transport mechanism session is no longer functional, the method
     * returns FALSE. It is the responsibility of the developer to handle this
     * case and restart the Transport mechanism manually.
     *
     * @example
     *
     *   if (!$transport->ping()) {
     *      $transport->stop();
     *      $transport->start();
     *   }
     *
     * The Transport mechanism will be started, if it is not already.
     *
     * It is undefined if the Transport mechanism attempts to restart as long as
     * the return value reflects whether the mechanism is now functional.
     *
     * @return bool TRUE if the transport is alive
     */
    public function ping()
    {
        return true;
    }
}
