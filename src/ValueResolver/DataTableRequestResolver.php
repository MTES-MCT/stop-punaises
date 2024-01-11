<?php

namespace App\ValueResolver;

use App\Dto\DataTableRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DataTableRequestResolver implements ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argumentMetadata)
    {
        return DataTableRequest::class === $argumentMetadata->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($this->supports($request, $argument)) {
            yield new DataTableRequest(
                draw: $request->get('draw'),
                start: $request->get('start'),
                length: $request->get('length'),
                columns: $request->get('columns'),
                order: $request->get('order'),
            );
        }
    }
}
