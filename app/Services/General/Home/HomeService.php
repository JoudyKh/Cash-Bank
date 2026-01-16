<?php

namespace App\Services\General\Home;

use App\Models\Info;
use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use App\Services\Admin\Info\InfoService;
use App\Services\General\Teacher\TeacherService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\SectionResource;
use App\Http\Requests\UpdateInfoRequest;

class HomeService
{
    public function __construct(protected InfoService $infoService )
    {
    }
//    public function getHome()
//    {
//        $data['info'] = $this->infoService->getAll();
//        return success($data);
//    }


}
