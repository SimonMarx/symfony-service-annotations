<?php


namespace SimonMarx\Symfony\Bundles\ServiceAnnotations\ArgumentResolver;


use Doctrine\Common\Annotations\AnnotationReader;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\DependencyInjection;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Exception\RuntimeException;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Utils\ReflectionCacheTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @TODO: Idea of Value Resolver for annotated controllers
 */
class DependencyInjectionAnnotationValueResolver implements ArgumentValueResolverInterface
{
    use ReflectionCacheTrait;

    private AnnotationReader $annotationReader;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->annotationReader = new AnnotationReader();
        $this->container = $container;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return false;
        $annotated = $this->getAnnotationForArgument($argument, $request);

        return null !== $annotated;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $annotation = $this->getAnnotationForArgument($argument, $request);

        if (null === $annotation) {
            throw new RuntimeException('Cant find annotation for argument "%s"', $argument->getName());
        }

        if (null !== $annotation->getServiceId()) {
            if ($this->container->has($annotation->getServiceId())) {

            }
        } elseif (null !== $annotation->getTagged()) {

        }
    }

    private function getAnnotationForArgument(ArgumentMetadata $argument, Request $request): ?DependencyInjection
    {
        list($controller, $action) = $this->getController($request);

        $rc = $this->getClassReflection($controller);

        if (null === $rc || false === $rc->hasMethod($action)) {
            return null;
        }

        $method = $rc->getMethod($action);

        $annotations = \array_filter(
            $this->annotationReader->getMethodAnnotations($method),
            fn(object $annotation) => $annotation instanceof DependencyInjection && $annotation->getTarget() === $argument->getName()
        );

        return array_values($annotations)[0] ?? null;
    }

    private function getController(Request $request): array
    {
        $controllerString = $request->get('_controller');
        $parts = \explode('::', $controllerString);

        if (\count($parts) === 1) {
            $parts[] = '__invoke';
        }

        return $parts;
    }
}