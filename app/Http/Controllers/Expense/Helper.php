<?php 
namespace App\Http\Controllers\Expense;
use App\Models\Expense\Cost;
use App\Models\Expense\SubmitCash;
use App\Models\Expense\Deposit;
use App\Models\Worker\WorkerPayment;
use App\Models\Staff\StaffPayment;
use App\Models\User;
use DB;

trait Helper {
  public $fields = [
      'Normal Cost' => [
        'description' => [
          'label' => 'Description',
          'type'  => 'text',
          'autocomplete' => 'costs.description',
          'optional' => false,
        ],
        'amount' => [
          'label' => 'Amount',
          'type'  => 'text',
          'optional' => false,
        ],
      ],
      'Submit Cash' => [
        'description' => [
          'label' => 'Description',
          'type'  => 'text',
          'autocomplete' => 'submit_cashes.description',
          'optional' => true,
        ],
        'through' => [
          'type' => 'text',
          'label' => 'Via',
          'autocomplete' => 'submit_cashes.through',
          'optional' => false,
        ],
        'amount' => [
          'label' => 'Amount',
          'type'  => 'text',
          'optional' => false,
        ],
      ],
      'Staff Payment' => [
        'staff_id' => [
          'type' => 'select',
          'label' => 'Staff',
          'optional' => false,
        ],
        'description' => [
          'label' => 'Description',
          'type'  => 'text',
          'autocomplete' => 'staff_payments.description',
          'optional' => true,
        ],
        'amount' => [
          'label' => 'Amount',
          'type'  => 'number',
          'optional' => false,
        ],
      ],
      'Deposit' => [
        'bank_account_id' => [
          'type' => 'select',
          'label' => 'Bank account',
          'optional' => false,
        ],
        'through' => [
          'type' => 'text',
          'label' => 'Via',
          'autocomplete' => 'deposits.through',
          'optional' => false,
        ],
        'description' => [
          'label' => 'Description',
          'type'  => 'text',
          'autocomplete' => 'deposits.description',
          'optional' => true,
        ],
        'amount' => [
          'label' => 'Amount',
          'type'  => 'text',
          'optional' => false,
        ],
      ],
      'Worker Payment' => [
        'worker_id' => [
          'type' => 'select',
          'label' => 'Worker',
          'optional' => false,
        ],
        'description' => [
          'label' => 'Description',
          'type'  => 'text',
          'autocomplete' => 'worker_payments.description',
          'optional' => true,
        ],
        'amount' => [
          'label' => 'Amount',
          'type'  => 'text',
          'optional' => false,
        ],
      ],
    ];
    
    public $models = [
      'Normal Cost' => Cost::class,
      'Submit Cash' => SubmitCash::class,
      'Deposit' => Deposit::class,
      'Worker Payment' => WorkerPayment::class,
      'Staff Payment' => StaffPayment::class,
    ];
    
    public $modules = ['normal_cost', 'submit_cash', 'deposit', 'worker_payment', 'staff_payment'];
    public $full_modules = ['Normal Cost', 'Submit Cash', 'Deposit', 'Staff Payment', 'Worker Payment'];
    
    public $user_permissions = [
      'Normal Cost' => [
        'create' => 'create_cost',
        'update' => 'update_cost',
        'delete' => 'delete_cost',
      ],
      'Submit Cash' => [
        'create' => 'create_submit_cash',
        'update' => 'update_submit_cash',
        'delete' => 'delete_submit_cash',
      ],
      'Deposit' => [
        'create' => 'create_deposit',
        'update' => 'update_deposit',
        'delete' => 'delete_deposit',
      ],
      'Worker Payment' => [
        'create' => 'create_worker_payment',
        'update' => 'update_worker_payment',
        'delete' => 'delete_worker_payment',
      ],
      'Staff Payment' => [
        'create' => 'create_staff_payment',
        'update' => 'update_staff_payment',
        'delete' => 'delete_staff_payment',
      ],
    ];
    
    public $rules = [
        'Normal Cost' => [
          'description' => 'required',
          'amount' => 'required',
        ],
        'Submit Cash' => [
          'description' => 'nullable',
          'amount' => 'required',
          'through' => 'required',
        ],
        'Deposit' => [
          'description' => 'nullable',
          'amount' => 'required',
          'through' => 'required',
          'bank_account_id' => 'required',
        ],
        'Worker Payment' => [
          'description' => 'nullable',
          'amount' => 'required',
          'worker_id' => 'required',
        ],
        'Staff Payment' => [
          'description' => 'nullable',
          'amount' => 'required',
          'staff_id' => 'required',
        ],
      ];
    
    public function getSummary() {
      $addSelect = [];
      foreach ($this->models as $key => $class) {
        $addSelect[$key] = (new $class)->selectRaw('SUM(amount)')->limit(1);
      }
      $result = User::orderByDesc('id')->select('id')->addSelect($addSelect)->first()->toArray();
      unset($result['id']);
      unset($result['profile_photo_url']);
      $total = 0;
      foreach ($result as $item => $val){
        $total+= $val;
      }
      $result['Total'] = $total;
      return $result;
    }
    
    public function getFields(){
      $cost_type = request()->input('cost_type');
      $fields = $this->fields[$cost_type];
      if($cost_type == 'Deposit'){
        $fields['bank_account_id']['options'] = parse_input_options(\App\Models\Company\BankAccount::select('id', 'bank_name')->get(), 'id', 'bank_name');
      }
      if($cost_type == 'Worker Payment'){
        $fields['worker_id']['options'] = parse_input_options(\App\Models\Worker\Worker::select('id', 'name')->get());
      }
      if($cost_type == 'Staff Payment'){
        $fields['staff_id']['options'] = parse_input_options(\App\Models\Staff\Staff::select('id', 'name')->get());
      }
      return $fields;
    }
    
    
  public function prepareQuery(){
    $param = $this->getParams();
    $blank = Cost::where('costs.id', null)->join('users', 'users.id', 'costs.user_id')
            ->when($param['search'], function($query, $text){
              $query->where('costs.description', 'like', "%$text%")
                    ->orWhere('costs.amount', 'like', "%$text%");
            })
            ->select('costs.id', 'costs.date', 'costs.description', 'costs.amount', DB::raw("CONCAT('Normal Cost') AS record"), DB::raw("CONCAT('null') AS through"), 'users.name AS author_name', 'costs.created_at');
    
    $normal_cost = Cost::join('users', 'users.id', 'costs.user_id')
            ->when($param['search'], function($query, $text){
              $query->where('costs.description', 'like', "%$text%")
                    ->orWhere('costs.amount', 'like', "%$text%");
            })
            ->select('costs.id', 'costs.date', 'costs.description', 'costs.amount', DB::raw("CONCAT('Normal Cost') AS record"), DB::raw("CONCAT('null') AS through"), 'users.name AS author_name', 'costs.created_at');
            
    $worker_payment = WorkerPayment::join('users', 'users.id', 'worker_payments.user_id')
            ->join('workers', 'workers.id', 'worker_payments.worker_id')
            ->when($param['search'], function($query, $text){
              $query->where('worker_payments.description', 'like', "%$text%")
                    ->orWhere('worker_payments.amount', 'like', "%$text%");
            })
            ->select('worker_payments.id', 'worker_payments.date', DB::raw("CONCAT(workers.name,' (',worker_payments.description, ')') AS description"), 'worker_payments.amount', DB::raw("CONCAT('Worker Payment') AS record"), DB::raw("CONCAT('null') AS through"), 'users.name AS author_name', 'worker_payments.created_at');
            
    $staff_payment = StaffPayment::join('users', 'users.id', 'staff_payments.user_id')
            ->join('staffs', 'staffs.id', 'staff_payments.staff_id')
            ->when($param['search'], function($query, $text){
              $query->where('staff_payments.description', 'like', "%$text%")
                    ->orWhere('staff_payments.amount', 'like', "%$text%");
            })
            ->select('staff_payments.id', 'staff_payments.date', DB::raw("CONCAT(staffs.name,' (',staff_payments.description, ')') AS description"), 'staff_payments.amount', DB::raw("CONCAT('Staff Payment') AS record"), DB::raw("CONCAT('null') AS through"), 'users.name AS author_name', 'staff_payments.created_at');
      
    $submit_cash = SubmitCash::join('users', 'users.id', 'submit_cashes.user_id')
            ->when($param['search'], function($query, $text){
              $query->where('submit_cashes.description', 'like', "%$text%")
                    ->orWhere('submit_cashes.amount', 'like', "%$text%")
                    ->orWhere('submit_cashes.through', 'like', "%$text%");
            })
             ->select('submit_cashes.id', 'submit_cashes.date', "submit_cashes.description", 'submit_cashes.amount', DB::raw("CONCAT('Submit Cash') AS record"), 'submit_cashes.through',  'users.name AS author_name', 'submit_cashes.created_at');
    
    $deposit = Deposit::join('users', 'users.id', 'deposits.user_id')
             ->join('bank_accounts', 'deposits.bank_account_id', 'bank_accounts.id')
            ->when($param['search'], function($query, $text){
              $query->where('deposits.description', 'like', "%$text%")
                    ->orWhere('deposits.amount', 'like', "%$text%")
                    ->orWhere('deposits.through', 'like', "%$text%");
            })
             ->select('deposits.id', 'deposits.date', DB::raw("CONCAT(deposits.description,' (',bank_accounts.bank_name, ')') AS description"), 'deposits.amount', DB::raw("CONCAT('Deposit') AS record"), 'deposits.through', 'users.name AS author_name', 'deposits.created_at');
    
    foreach ($param['modules'] as $item){
      $blank = $blank->union($$item);
    }
    return $blank->orderBy('created_at', 'DESC');
  }
  
  public function getParams() {
    return [
      'per_page' => request()->input('per_page') ?? 5,
      'search' => request()->input('search') ?? '',
      'modules' => request()->input('modules') ?? $this->modules,
    ];
  }
}