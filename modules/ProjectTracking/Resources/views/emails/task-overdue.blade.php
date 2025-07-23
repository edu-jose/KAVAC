@component('mail::message')
La siguiente tarea bajo tu responsabilidad ha sobrepasado su fecha límite:

**Proyecto:** {{ $task->project->name ?? 'N/A' }}  
**Subproyecto:** {{ $task->subproject_name ?? 'N/A' }}  
**Tarea:** {{ $task->name }}  
**Descripción:** {{ $task->description }}  
**Fecha Inicio:** {{ $task->start_date }}  
**Fecha Límite:** {{ $task->end_date }}  
**Días de retraso:** {{ $delayDays }}  

@component('mail::button', ['url' => route('projecttracking.tasks.edit', $task->id)])
Ver Tarea
@endcomponent

Por favor, actualiza el estado de esta tarea lo antes posible.

Saludos,  
{{ $fromEmail }}

@endcomponent