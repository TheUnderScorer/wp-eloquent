<?php

namespace UnderScorer\ORM\Tests\ORM\Models;

use UnderScorer\ORM\Models\User;
use UnderScorer\ORM\Tests\TestCase;

/**
 * Class UserTest
 * @package UnderScorer\ORM\Tests\ORM\Models
 */
final class UserTest extends TestCase
{

    /**
     * @covers User::getAttribute()
     */
    public function testAliasesGetters(): void
    {
        /**
         * @var User $user
         */
        $user = $this->userFactory->create();

        $user->meta->first_name = 'John';
        $user->meta->last_name  = 'Lemon';

        $user->user_login = 'johnny';
        $user->user_email = 'johnny@gmail.com';

        $user->save();

        $this->checkAliases( $user );
    }

    /**
     * @param User $user
     *
     * @return void
     */
    protected function checkAliases( User $user ): void
    {
        $this->assertEquals(
            $user->meta->first_name,
            $user->firstName
        );

        $this->assertEquals(
            $user->meta->last_name,
            $user->lastName
        );

        $this->assertEquals(
            $user->user_login,
            $user->login
        );

        $this->assertEquals(
            $user->user_email,
            $user->email
        );
    }

    /**
     * @covers User::setAttribute()
     */
    public function testAliasesSetters(): void
    {
        /**
         * @var User $user
         */
        $user = $this->userFactory->create();

        $user->login     = 'johnny';
        $user->email     = 'johnny@gmail.com';
        $user->firstName = 'John';
        $user->lastName  = 'Lemon';

        $user->save();

        $this->checkAliases( $user );

    }

}
