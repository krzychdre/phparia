<?php

/*
 * Copyright 2014 Brian Smith <wormling@gmail.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace phparia\Client;

use Pest_BadRequest;
use Pest_InvalidRecord;
use Pest_NotFound;
use phparia\Events\Event;
use phparia\Exception\InvalidParameterException;
use phparia\Exception\NotFoundException;
use phparia\Exception\UnprocessableEntityException;

/**
 * Events API
 *
 * @author Brian Smith <wormling@gmail.com>
 */
class Events extends Base
{

    /**
     * WebSocket connection for events.
     * 
     * @param string $app (required) Applications to subscribe to.  Allows comma separated values.
     * @return Event[]
     */
    public function events($app)
    {
        $uri = '/events';
        $response = $this->client->getEndpoint()->get($uri, array(
            'app' => $app,
        ));

        $events = [];
        foreach ($response as $event) {
            $events[] = new Event($this->client, $event);
        }

        return $events;
    }

    /**
     *
     * @param string $eventName Event name
     * @param string $application (required) The name of the application that will receive this event
     * @param string $source URI for event source (channel:{channelId}, bridge:{bridgeId}, endpoint:{tech}/{resource}, deviceState:{deviceName}  Allows comma separated values.
     * @param array $variables The "variables" key in the body object holds custom key/value pairs to add to the user event. Ex. { "variables": { "key": "value" }
     * }
     * @throws InvalidParameterException
     * @throws NotFoundException
     * @throws UnprocessableEntityException
     */
    public function createUserEvent($eventName, $application, $source, $variables = array())
    {
        $uri = "/events/user/$eventName";
        try {
            $this->client->getEndpoint()->post($uri, array(
                'application' => $application,
                'source' => $source,
                'variables' => $variables,
            ));
        } catch (Pest_BadRequest $e) { // Invalid parameters
            throw new InvalidParameterException($e);
        } catch (Pest_NotFound $e) { // Channel not found
            throw new NotFoundException($e);
        } catch (Pest_InvalidRecord $e) { // Channel not in Stasis application
            throw new UnprocessableEntityException($e);
        }
    }

}
