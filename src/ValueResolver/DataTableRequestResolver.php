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
        $order = $request->get('order');
        if (!is_array($order)) {
            return;
        }
        $orderList = [];
        foreach ($order as $orderItem) {
            if (!isset($orderItem['column']) || !is_numeric($orderItem['column'])) {
                $orderItem['column'] = 0;
            }

            if (!isset($orderItem['dir']) || !in_array(strtolower($orderItem['dir']), ['asc', 'desc'])) {
                $orderItem['dir'] = 'desc';
            }
            $orderList[] = $orderItem;
        }
        if ($this->supports($request, $argument)) {
            yield new DataTableRequest(
                draw: $request->get('draw'),
                start: $request->get('start'),
                length: $request->get('length'),
                columns: $request->get('columns'),
                order: $orderList,
            );
        }
    }
}
