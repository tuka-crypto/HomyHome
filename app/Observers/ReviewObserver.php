<?php
namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function created(Review $review)
    {
        $apartment = $review->apartment;
        $average = $apartment->reviews()->avg('rating');

        $apartment->avarage_rating = $average;
        $apartment->save();
    }
    public function updated(Review $review)
    {
        $apartment = $review->apartment;
        $average = $apartment->reviews()->avg('rating');

        $apartment->avarage_rating = $average;
        $apartment->save();
    }
    public function deleted(Review $review)
    {
        $apartment = $review->apartment;
        $average = $apartment->reviews()->avg('rating');

        $apartment->avarage_rating = $average;
        $apartment->save();
    }
}