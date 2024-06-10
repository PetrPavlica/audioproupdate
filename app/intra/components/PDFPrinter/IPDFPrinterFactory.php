<?php

namespace Intra\Components\PDFPrinter;

interface IPDFPrinterFactory {

    /** @return PDFPrinterControl */
    function create();
}
