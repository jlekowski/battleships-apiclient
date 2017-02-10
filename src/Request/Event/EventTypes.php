<?php

namespace BattleshipsApi\Client\Request\Event;

final class EventTypes
{
    const EVENT_TYPE_CHAT = 'chat';
    const EVENT_TYPE_SHOT = 'shot';
    const EVENT_TYPE_JOIN_GAME = 'join_game';
    const EVENT_TYPE_START_GAME = 'start_game';
    const EVENT_TYPE_NAME_UPDATE = 'name_update';
    const EVENT_TYPE_NEW_GAME = 'new_game';
}
