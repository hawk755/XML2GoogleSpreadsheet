<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\XML2GoogleSpreadsheet;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'import',
    description: 'Imports XML file to a Google Spreadsheet',
    hidden: false
)]
class XML2GoogleSpreadsheetCommand extends Command
{
    public function __construct(private XML2GoogleSpreadsheet $xml2GoogleSpreadsheet)
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Local or remote XML file path/URL');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->xml2GoogleSpreadsheet->import($input->getArgument('file')) ? Command::SUCCESS : Command::FAILURE;
    }
}
