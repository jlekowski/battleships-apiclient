<?php

namespace BattleshipsApi\Client\Request\Event;

final class EventTypes
{
    const TYPES = [
        'CHAT' => 'chat',
        'SHOT' => 'shot',
        'JOIN_GAME' => 'join_game',
        'START_GAME' => 'start_game',
        'NAME_UPDATE' => 'name_update',
        'NEW_GAME' => 'new_game'
    ];
}
