<?php

namespace Tests\Unit\Models;

use App\Models\BaseModel;
use App\Models\BidderRound;
use App\Models\Offer;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This test takes care of all methods and business logic of the {@link User}.
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testIsNewMember()
    {
        /** @var User $user */
        $user = User::factory()->create()->first();
        $user->joinDate = Carbon::now();

        $this->assertTrue($user->isNewMember);
    }

    public function testOffers()
    {
        $user = $this->createAndActAsUser();

        $offers = $this->createOffers($user);

        $this->assertCount(7, $user->offers);

        $this->assertSame($offers->first()->{BaseModel::COL_ID}, $user->offers->first()->{BaseModel::COL_ID});
    }

    public function testOffersForRound()
    {
        $user = $this->createAndActAsUser();

        /** @var Topic $topic */
        $topic = Topic::factory()->for(BidderRound::factory())->create();

        $this->assertEmpty($user->offersForTopic($topic)->get());

        $offers = $this->createOffers($user, $topic);
        $this->assertNotEmpty($user->offersForTopic($topic)->get());
        $this->assertEquals($offers->count(), $offers->intersect($user->offersForTopic($topic)->get())->count());
    }

    protected function createOffers(User $user, ?Topic $topic = null): Collection
    {
        return Offer::factory()
            ->count(7)
            ->make()
            ->each(function (Offer $offer) use ($user, $topic) {
                $offer->user()->associate($user)->save();
                if (isset($topic)) {
                    $offer->topic()->associate($topic)->save();
                }
            });
    }
}
