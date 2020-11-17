<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\DependencyInjection\Compiler;


use App\DocReader\AnnotationHandler\RouteAnnotationHandler;
use App\Event\Doctrine\UserPrePersistListener;
use App\Serializer\Normalizer\DateTimeNormalizer;
use App\Service\DocReader\Controller\DocumentationController;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\EventListener;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTag;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\CompilerPassServiceAnnotationTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\InstanceOfInjectionTrait;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ServiceTagPass implements CompilerPassInterface
{
    use ReflectionCacheTrait;
    use CompilerPassServiceAnnotationTrait;
    use InstanceOfInjectionTrait;

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if (
                false === $this->definitionHasValidClass($definition)
                || (
                    false === $this->definitionHasAnnotation($definition, ServiceTag::class)
                    && false === $this->definitionHasAnnotation($definition, EventListener::class)
                )
            ) {
                continue;
            }

            /** @var ServiceTag[] $tags */
            $tags = $this->getDefinitionAnnotations($definition, ServiceTag::class);
            /** @var EventListener[] $listeners */
            $listeners = $this->getDefinitionAnnotations($definition, EventListener::class);

            foreach ($listeners as $listener) {
                $definition->addTag($listener->getEventTag(), $listener->getAttributes());
            }

            foreach ($tags as $tag) {
                $definition->addTag($tag->getName(), $tag->getAttributes());
            }
        }
    }
}