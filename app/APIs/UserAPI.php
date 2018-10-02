<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/23/15
 * Time: 8:09 PM
 */

namespace App\APIs;


use App\ModelFactory\UserFactory;
use App\Models\ObjectMetaData;
use App\Models\RewardConstraint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class UserAPI extends NeedAuthAPI
{

    public function postNew($userType = null) {
        if($userType == 'advertiser') {
            $this->permission(array('admin'));
        } else {
            $this->permission(array());
        }


        $data = $this->getDataSubmitted();

        $user = UserFactory::create()
            ->bind($data, array('role' => $userType, 'created_by' => $this->currentUser->id))
            ->save();

        if($user->saved()) {
            return $this->_data($user->getObject());
        } else {
            throw new \Exception($user->error()[0], 1);
        }
    }

    public function postUpdate() {
        $data = $this->getDataSubmitted();

        $id = (isset($data['id'])) ? $data['id'] : $this->currentUser->id;

        $users = UserFactory::find(array(
            'conditions' => array(
                array('where' => array('id', $id))
            )
        ));

        if($users->count() == 0) {
            throw new \Exception('User not found', 1);
        }

        $user = $users->get(0);

        if($user->getObject()->created_by != $this->currentUser->id && $user->getObject()->id != $this->currentUser->id
            && ($user->getObject()->role != 'affliater' || $this->currentUser->role != 'admin')) {
            $this->permission(array());
        }

        if($user->getObject()->role == 'affliater') {
            unset($data['source_name']);
        }

        $user->bind($data);
        $user->save();

        if(!$user->saved()) {
            throw new \Exception($user->error()[0], 1);
        }

        if($this->currentUser->role == 'admin' && ($level = $this->getData('level', null)) != null) {
            $meta = ObjectMetaData::where('object_type', 'user')
                ->where('object_id', $user->getObject()->id)
                ->where('key', 'level')
                ->first();
            if(empty($meta)) {
                $meta = new ObjectMetaData();
                $meta->object_type = 'user';
                $meta->object_id = $user->getObject()->id;
                $meta->key = 'level';
            }
            $meta->value = $level;
            $meta->save();
        }
        return array('data' => $user->getObject());

    }

    public function getList() {
        $this->permission(['admin']);

        $page = $this->request->get('page', 1);
        $keyword = $this->request->get('keyword');
//        Paginator::currentPageResolver(function() use ($page){
//            return $page;
//        });
//        $users = User::orderBy('id', 'DESC')->simplePaginate(config('app_init.pagination.itemsPerPage'))->all();
        $users = User::where('role', 'LIKE', "$keyword%")
            ->orWhere('short_name', 'LIKE', "$keyword%")
            ->orWhere('name', "$keyword%")
            ->orWhere('email', "$keyword%")
            ->orderBy('id', 'DESC')
            ->get();
        foreach($users as $user) {
            $user->user_type = $user->role();
            if($user->role == 'affliater') {
                $user->level = $user->getLevelText();
            }
        }
        return array('data' => $users);
    }

    public function postRewardSetting() {
        $this->permission(['admin']);

        $data = $this->getDataSubmitted();

        $userId = $this->getData('id', 0);
        $user = User::find($userId);

        if(empty($user)) {
            throw new \Exception(trans('errors.not_found', ['item' => 'User']), 1);
        }

        $keys = array_keys($data);

        $constraints = array();

        foreach($keys as $key) {
            if(preg_match('/from.([0-9]+)/', $key, $matches)) {
                $num = $matches[1];
                $constraints[] = array(
                    'from' => $this->getData('from.'.$num, null),
                    'to' => $this->getData('to.'.$num, null),
                    'unitPrice' => $this->getData('unit_price.'.$num, null),
                );
            }
        }

        // Validating

        for($i = 0; $i<count($constraints); $i++) {
            $constraintOne = $constraints[$i];
            for($j = 0; $j<count($constraints); $j++) {
                if($i == $j) continue;
                $constraintTwo = $constraints[$j];
                if($constraintOne['from'] >= $constraintTwo['from']  && $constraintOne['from'] <= $constraintTwo['to']
                || $constraintOne['to'] >= $constraintTwo['from']  && $constraintOne['to'] <= $constraintTwo['to']) {
                    throw new \Exception('Constraints are conflicted. Please check again
                     From:'.$constraintOne['from'].' - To:'.$constraintOne['to'].' conflicted with
                      From:'.$constraintTwo['from'].' - To:'.$constraintTwo['to']);
                }
            }
        }

        foreach($constraints as $constraint) {
            if($constraint['from'] > $constraint['to']) {
                throw new \Exception('Constraints are not valid. Please check again: From:'.$constraint['from'].' - To:'.$constraint['to']);
            }
        }

        RewardConstraint::where('user_id', $userId)->delete();

        $rewardConstraints = array();

        foreach($constraints as $constraint) {
            $newConstraint = new RewardConstraint();
            $newConstraint->user_id = $userId;

            $startTime = $constraint['from'];
            $endTime = $constraint['to'];

            $tmpStart = \DateTime::createFromFormat("Y/m/d", $startTime);
            $tmpEnd = \DateTime::createFromFormat("Y/m/d", $endTime);

            if($tmpStart) {
                $startTime = $tmpStart->format("Y-m-d");
            } else {
                $startTime = '01/01/0001';
            }
            if($tmpEnd) {
                $endTime = $tmpEnd->format("Y-m-d");
            } else {
                $endTime = '31/12/2099';
            }


            $startTime = date("Y-m-d 00:00:00", strtotime($startTime));
            $endTime = date("Y-m-d 23:59:59", strtotime($endTime));

            $newConstraint->from = $startTime;
            $newConstraint->to = $endTime;
            $newConstraint->unit_price = $constraint['unitPrice'];

            if(!$newConstraint->save()) {
                throw new \Exception(trans('errors.try_refresh'));
            }

            $rewardConstraints[] = $newConstraint;
        }


        return array('data' => $rewardConstraints);
    }

    public function getListParentUser() {
        $this->permission(['admin']);

        $tier2UserList = UserFactory::find([
            'conditions' => [
                ['where' =>['created_by', '>', '0']],
                ['where' =>['role', 'affliater']]
            ]
        ]);

        $parentIdArr = $tier2UserList->getArrayOf('created_by');

        $parentIdArr[] = 0;

        $users = $tier2UserList->getListObject();
        $parentUser = UserFactory::find([
            'conditions' => [
                ['whereRaw' => ['id IN ('.implode(',', $parentIdArr).')']]
            ],
            'orderBy' => ['id', 'DESC']
        ]);

        foreach($users as $user) {
            $user->user_type = $user->role();
            $user->level = $user->getLevelText();
            $user->parent_name = $parentUser->get($user->created_by)->getObject()->short_name;
        }

        return $this->_data($users);

    }

    public function getNameByShortname() {
        $this->permission(['admin']);

        $id = $this->request->get('short_name');

        $user = User::where('short_name', $id)->first();

        if(empty($user)) {
            return $this->_data(['name' => 'Not found', 'id' => '-1']);
        }
        return $this->_data(['name' => $user->name, 'id' => $user->id]);
    }

    public function getBankInfo() {
        return $this->_data(User::all(['short_name', 'financial_name', 'branch_name', 'deposits', 'account_number', 'account_holder', 'oversea_account']));
    }
}