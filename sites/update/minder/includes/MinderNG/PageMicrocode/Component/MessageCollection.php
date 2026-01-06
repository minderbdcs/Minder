<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Collection\Collection;

class MessageCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return Message::CLASS_NAME;
    }

    public function removeObsolete() {
        $this->remove(iterator_to_array($this->getObsolete()));
    }

    public function getObsolete() {
        return $this->filter(function(Message $message){
            return $message->ttl < (time() - $message->issued);
        });
    }

    public function addMessage($message, $tts = Message::DEFAULT_TTS, $ttl = Message::DEFAULT_TTL) {
        return $this->_doAdd($message, $tts, $ttl, Message::TYPE_INFO);
    }

    public function addError($message, $tts = Message::DEFAULT_TTS, $ttl = Message::DEFAULT_TTL) {
        return $this->_doAdd($message, $tts, $ttl, Message::TYPE_ERROR);
    }

    public function addWarning($message, $tts = Message::DEFAULT_TTS, $ttl = Message::DEFAULT_TTL) {
        return $this->_doAdd($message, $tts, $ttl, Message::TYPE_WARNING);
    }

    /**
     * @param bool $full
     * @return \Iterator
     */
    public function getArrayCopy($full = false)
    {
        return parent::getArrayCopy($full);
    }


    private function _doAdd($message, $tts, $ttl, $type) {
        return $this->add(array(array(
            Message::FIELD_MESSAGE => $message,
            Message::FIELD_TTS => $tts,
            Message::FIELD_TTL => $ttl,
            Message::FIELD_TYPE => $type,
        )), new AddOptions(false, true));
    }
}