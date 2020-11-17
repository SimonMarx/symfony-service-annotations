<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use App\Repository\ContextRepository\UserContextRepository;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceCall;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class ServiceCallPass implements CompilerPassInterface
{
    use ReflectionCacheTrait;
    use CompilerPassServiceAnnotationTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (false === $this->definitionHasValidClass($definition)) {
                continue;
            }

            $rc = $this->getClassReflection($definition->getClass());

            foreach ($rc->getMethods() as $method) {
                if ($annotation = $this->annotationReader->getMethodAnnotation($method, ServiceCall::class)) {
                    $parameters = [];
                    foreach ($method->getParameters() as $parameter) {
                        if (null === $parameter->getClass()) {
                            $value = $parameter->getDefaultValue();
                            \settype($value, $parameter->getType());
                        } else {
                            $value = $parameter->getClass()->getName();
                        }

                        try {
                            $container->findDefinition($value);
                            $value = new Reference($value);
                        } catch (ServiceNotFoundException $serviceNotFoundException) {
                        }

                        $parameters[$parameter->getPosition()] = $value;
                    }

                    $definition->addMethodCall($method->getName(), $parameters);
                }
            }
        }
    }
}