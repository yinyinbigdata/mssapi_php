<?php
/**
 * Copyright 2010-2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Mss\Common\Facade;

use Mss\Common\Mss;

/**
 * Base facade class that handles the delegation logic
 *
 * @deprecated "Facades" are being removed in version 3.0 of the SDK.
 */
abstract class Facade implements FacadeInterface
{
    /** @var Mss */
    protected static $serviceBuilder;

    /**
     * Mounts the facades by extracting information from the service builder config and using creating class aliases
     *
     * @param string|null $targetNamespace Namespace that the facades should be mounted to. Defaults to global namespace
     *
     * @param Mss $serviceBuilder
     */
    public static function mountFacades(Mss $serviceBuilder, $targetNamespace = null)
    {
        self::$serviceBuilder = $serviceBuilder;
        require_once __DIR__ . '/facade-classes.php';
        foreach ($serviceBuilder->getConfig() as $service) {
            if (isset($service['alias'], $service['class'])) {
                $facadeClass = __NAMESPACE__ . '\\' . $service['alias'];
                $facadeAlias = ltrim($targetNamespace . '\\' . $service['alias'], '\\');
                if (!class_exists($facadeAlias) && class_exists($facadeClass)) {
                    // @codeCoverageIgnoreStart
                    class_alias($facadeClass, $facadeAlias);
                    // @codeCoverageIgnoreEnd
                }
            }
        }
    }

    /**
     * Returns the instance of the client that the facade operates on
     *
     * @return \Mss\Common\Client\MssClientInterface
     */
    public static function getClient()
    {
        return self::$serviceBuilder->get(static::getServiceBuilderKey());
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::getClient(), $method), $args);
    }
}
