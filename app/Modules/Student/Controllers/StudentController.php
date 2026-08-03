<?php

namespace Modules\Student\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Student\Actions\CreateStudentAction;
use Modules\Student\Actions\DeleteStudentAction;
use Modules\Student\Actions\UpdateParentProfileAction;
use Modules\Student\Actions\UpdateStudentAction;
use Modules\Student\Models\Student;
use Modules\Student\Requests\StoreStudentRequest;
use Modules\Student\Requests\UpdateParentProfileRequest;
use Modules\Student\Requests\UpdateStudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Student\Actions\FindParentByPhoneAction;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('modules.student.students.index', [
            'students' => Student::query()->with('parentProfile')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.student.students.create');
    }

    public function store(StoreStudentRequest $request, CreateStudentAction $action): RedirectResponse
    {
        $result = $action->execute($request->studentData(), $request->parentData());

        $status = $result['parent_was_reused']
            ? "Siswa {$result['student']->full_name} berhasil ditambahkan. Nomor HP orang tua sudah terdaftar sebelumnya, siswa ini otomatis ditautkan ke data orang tua yang sama."
            : "Siswa {$result['student']->full_name} berhasil ditambahkan.";

        return redirect()->route('students.index')->with('status', $status);
    }

    public function edit(Student $student): View
    {
        return view('modules.student.students.edit', [
            'student' => $student->load('parentProfile.students'),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student, UpdateStudentAction $action): RedirectResponse
    {
        $action->execute($student, $request->validated());

        return redirect()->route('students.edit', $student)->with('status', 'Data siswa berhasil diperbarui.');
    }

    public function updateParent(UpdateParentProfileRequest $request, Student $student, UpdateParentProfileAction $action): RedirectResponse
    {
        $action->execute($student->parentProfile, $request->validated());

        return redirect()->route('students.edit', $student)->with('status', 'Data orang tua berhasil diperbarui.');
    }

    public function destroy(Student $student, DeleteStudentAction $action): RedirectResponse
    {
        $action->execute($student);

        return redirect()->route('students.index')->with('status', 'Data siswa berhasil dihapus.');
    }
    
    // Fitur lookup orang tua berdasarkan nomor HP, untuk mempermudah pengisian form tambah siswa baru.
    // Jika nomor HP sudah ada di database, maka data orang tua akan otomatis diisi dengan data yang sudah ada, dan siswa baru akan ditautkan ke orang tua yang sama.
    public function parentLookup(Request $request, FindParentByPhoneAction $action): JsonResponse
    {
        $request->validate(['phone_number' => ['required', 'string']]);

        $parent = $action->execute($request->query('phone_number'));

        if (! $parent) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'father_name' => $parent->father_name,
            'mother_name' => $parent->mother_name,
            'address' => $parent->address,
            'children' => $parent->students->pluck('full_name'),
        ]);
    }
}
