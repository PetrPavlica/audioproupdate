<?php

namespace Intra\Components\MultiUploader;

interface IMultiUploaderControlFactory {

    /** @return MultiUploaderControl */
    function create();
}
