@props(['contents', 'headings'])

@php
  $class_dir = __('direction') === 'rtl' ? 'pr-4' : 'pl-4';
@endphp

<tbody class="divide-y-[1px] divide-black/10 dark:divide-white/10">
  @if (count($contents) == 0)
    <tr id="no-results-row">
      <td colspan="{{ $headings + 1 }}"
        class="py-10 text-center text-sm text-black transition-all duration-300 hover:bg-black/10 dark:text-white dark:hover:bg-white/10">
        {{ __('No results.') }}
      </td>
    </tr>
  @endif

  @foreach ($contents as $key => $content)
    <tr class="transition-all duration-300 hover:bg-black/10 dark:hover:bg-white/10">
      @foreach ($content as $key => $column)
        @if ($key == 'actions')
          @continue
        @endif
        <td class="{{ $class_dir }} py-2 text-sm font-medium">{{ $column }}</td>
      @endforeach

      @if ($content['actions'])
        <td class="px-5">
          <a class="group inline-block cursor-pointer" data-toggle="dropdown">
            <x-icons.dots class="h-6 w-6 rounded-sm transition" />
          </a>
          <x-dropdown>
            @foreach ($content['actions'] as $name => $route)
              @php
                $isDelete = $name == __('Delete');
                $isRestore = $name == __('Restore');
              @endphp
              @if ($isDelete)
                <x-dropdown-link data-toggle="delete" data-method="DELETE" data-id="{{ $content['id'] ?? '' }}" href="{{ $route }}">
                  {{ $name }}
                </x-dropdown-link>
              @elseif ($isRestore)
                <form method="POST" action="{{ $route }}">
                  @csrf
                  <button type="submit"
                    class="cursor-pointer py-1 text-left w-full sm:px-2 sm:hover:rounded-sm sm:hover:bg-black/10 sm:dark:hover:bg-white/10 duration-200 transition-colors">
                    {{ $name }}
                  </button>
                </form>
              @else
                <x-dropdown-link href="{{ $route }}">
                  {{ $name }}
                </x-dropdown-link>
              @endif
            @endforeach
          </x-dropdown>
        </td>
      @endif
    </tr>
  @endforeach
</tbody>
