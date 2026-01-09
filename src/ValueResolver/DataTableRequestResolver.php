<?php

namespace App\ValueResolver;

use App\Dto\DataTableRequest;
use App\Service\RequestDataExtractor;
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
        $queryData = $request->query->all();
        $order = RequestDataExtractor::getArray($queryData, 'order');
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
                draw: (int) RequestDataExtractor::getString($queryData, 'draw'),
                start: (int) RequestDataExtractor::getString($queryData, 'start'),
                length: (int) RequestDataExtractor::getString($queryData, 'length'),
                columns: RequestDataExtractor::getArray($queryData, 'columns'),
                order: $orderList,
            );
        }
    }
}
