<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 2/16/17
 * Time: 2:14 PM
 */

namespace App\Console\Commands;


use App\Models\Post;
use App\Models\PostHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GarbageDataCollect extends Command
{
    protected $signature = 'gcc:history';

    protected $description = 'Display an inspiring quote';

    protected $limit      = 1200;
    protected $countStop  = 0;
    protected $numberDate = -7;

    public function handle()
    {
        $aWeekAgo = Carbon::now()->addDays($this->numberDate)->toDateTimeString();

        while (true) {
            $historyToDelete = PostHistory::select('id')
                ->where('status', PostHistory::STATUS_DRAFT)
                ->where('created_at', '<=', $aWeekAgo)
                ->limit($this->limit);

            $rowCount = $historyToDelete->delete();

            if ($rowCount === $this->countStop) {
                $this->info("Stop: No row needs to be deleted");
                break;
            }

            $this->info($rowCount . " rows need to be deleted");
        }
    }
}
