<?php

namespace App\Service\Mailer;

interface MessageInterface
{
    public function getFrom();

    public function getTo();

    public function getTemplate(): Template;

    public function getParameters();
}
