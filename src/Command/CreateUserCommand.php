<?php

namespace App\Command;

use App\Action\CreateUserAction;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user',
)]
class CreateUserCommand extends Command
{
    /**
     * Instantiate the command.
     *
     * @param CreateUserAction   $action
     * @param ValidatorInterface $validator
     */
    public function __construct(
        protected CreateUserAction $action,
        protected UserRepository $userRepository,
    ) {
        parent::__construct();
        $this->action = $action;
        $this->userRepository = $userRepository;
    }

    /**
     * Run the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        // Prompt for the email address
        $question = new Question('Email Address: ');
        $email = $helper->ask($input, $output, $question);

        // Prompt for password
        $question = new Question('Enter Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        // Create the user
        $action = $this->action->run([
            'email' => $email,
            'password' => $password,
        ]);

        if ($action->failed()) {
            $io->error($action->getMessage());

            return Command::FAILURE;
        }

        $io->success($action->getMessage());

        return Command::SUCCESS;
    }
}
