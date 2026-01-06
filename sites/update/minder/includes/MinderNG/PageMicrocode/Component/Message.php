<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Model;

/**
 * Class Message
 * @package MinderNG\PageMicrocode\Component
 * @property string message
 * @property int ttl
 * @property int tts
 * @property int issued
 * @property string type
 */
class Message extends Model {
    protected static $_defaults = array(
        Message::FIELD_MESSAGE => '',
        Message::FIELD_TTL => Message::DEFAULT_TTL,
        Message::FIELD_TTS => Message::DEFAULT_TTS,
        Message::FIELD_TYPE => Message::TYPE_INFO
    );

    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Component\\Message';

    const ID_PREFIX = 'MESSAGE';

    const FIELD_MESSAGE = 'message';
    const FIELD_TTL = 'ttl';
    const FIELD_TTS = 'tts';
    const FIELD_ISSUED = 'issued';
    const FIELD_TYPE = 'type';

    const TYPE_INFO = 'INFO';
    const TYPE_ERROR = 'ERROR';
    const TYPE_WARNING = 'WARNING';

    const DEFAULT_TTS = 30; //time to show in seconds
    const DEFAULT_TTL = 300; //time to live in seconds

    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        $attributes[static::getIdAttribute()] = isset($attributes[static::getIdAttribute()]) ? $attributes[static::getIdAttribute()] : uniqid(static::ID_PREFIX, true);

        $attributes[static::FIELD_ISSUED] = isset($attributes[static::FIELD_ISSUED]) ? intval($attributes[static::FIELD_ISSUED]) : time();

        return $attributes;
    }


}