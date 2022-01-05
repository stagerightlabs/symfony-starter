<?php

namespace App\Action;

use StageRightLabs\Actions\Action;

class LuckyNumberAction extends Action
{
    public int $lucky;

    /**
     * Handle the action.
     *
     * @param Action|array $input
     * @return self
     */
    public function handle($input = [])
    {
        $this->lucky = random_int(0, 100);

        // Simulating a business logic check
        if ($this->lucky > 90) {
            return $this->fail('This is a simulated error.');
        }

        return $this->complete();
    }
}
