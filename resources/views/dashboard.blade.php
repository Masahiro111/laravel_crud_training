<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Upcoming weather') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div
                     x-data="weather()"
                     class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center">
                        <select x-model="city" @change="getWeather()"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">-- Select city --</option>
                            @foreach(config('app.cities') as $key => $name)
                            <option value="{{ $key }}">{{ Str::title($key) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <template x-if="loading">
                        <div class="loader bg-white p-5 my-4 rounded-full flex space-x-3 justify-center">
                            <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-gray-300 rounded-full animate-bounce"></div>
                        </div>
                    </template>

                    <template x-if="error != ''">
                        <div x-text="error" class="mt-4 text-red-600"></div>
                    </template>

                    <template x-if="!loading">
                        <div class="overflow-hidden overflow-x-auto mt-6 min-w-full align-middle sm:rounded-md">

                            <table class="min-w-full border divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <template x-for="i in 24">
                                            <th x-text="String(i - 1).padStart(2, '0')">
                                            </th>
                                        </template>

                                        {{--
                                        <template x-for="(time , id) in weather.time">
                                            <th class="px-6 py-3 bg-gray-50">
                                                <span
                                                      class="text-xs font-medium tracking-wider leading-4 text-left text-gray-500 uppercase"
                                                      x-text="time"></span>
                                            </th>
                                        </template> --}}

                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                    <tr class="bg-white">
                                        <template x-for="max in weather.temperature_2m">
                                            <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                Max. temp. <span x-text="max"></span>
                                            </td>
                                        </template>
                                    </tr>
                                    <tr class="bg-white">
                                        <template x-for="min in weather.temperature_2m">
                                            <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                Min. temp. <span x-text="min"></span>
                                            </td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </template>

                </div>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        const hoursOfDay = 24;

        const sliceArrays = (array, number) => [...Array(Math.ceil(array.length / number))].map((_, index) => array.slice(index * number, (index + 1) * number));

        const getDay = dateTimeStr => {
            const jstDate = new Date(new Date(dateTimeStr) + ((new Date().getTimezoneOffset() + (9 * 60)) * 60 * 1000));
            return ['日', '月', '火', '水', '木', '金', '土'][jstDate.getDay()];
        };

        const generateWeatherIcon = weatherCode => {
            // https://www.jodc.go.jp/data_format/weather-code_j.html
            const iconText = (() => {
                if (weatherCode === 0) return { text: '快晴', emoji: '☀' };  // 0 : Clear Sky
                if (weatherCode === 1) return { text: '晴れ', emoji: '🌤' };  // 1 : Mainly Clear
                if (weatherCode === 2) return { text: '一部曇', emoji: '⛅' };  // 2 : Partly Cloudy
                if (weatherCode === 3) return { text: '曇り', emoji: '☁' };  // 3 : Overcast
                if (weatherCode <= 49) return { text: '霧', emoji: '🌫' };  // 45, 48 : Fog And Depositing Rime Fog
                if (weatherCode <= 59) return { text: '霧雨', emoji: '🌧' };  // 51, 53, 55 : Drizzle Light, Moderate And Dense Intensity ・ 56, 57 : Freezing Drizzle Light And Dense Intensity
                if (weatherCode <= 69) return { text: '雨', emoji: '☔' };  // 61, 63, 65 : Rain Slight, Moderate And Heavy Intensity ・66, 67 : Freezing Rain Light And Heavy Intensity
                if (weatherCode <= 79) return { text: '雪', emoji: '☃' };  // 71, 73, 75 : Snow Fall Slight, Moderate And Heavy Intensity ・ 77 : Snow Grains
                if (weatherCode <= 84) return { text: '俄か雨', emoji: '🌧' };  // 80, 81, 82 : Rain Showers Slight, Moderate And Violent
                if (weatherCode <= 94) return { text: '雪・雹', emoji: '☃' };  // 85, 86 : Snow Showers Slight And Heavy
                if (weatherCode <= 99) return { text: '雷雨', emoji: '⛈' };  // 95 : Thunderstorm Slight Or Moderate ・ 96, 99 : Thunderstorm With Slight And Heavy Hail
                return { text: '不明', emoji: '✨' };
            })();
            return `<span title="${iconText.text}">${iconText.emoji}</span>`;
        };

        function weather() {
                return {
                    city: '',
                    weather: {},
                    loading: false,
                    error: '',
                    getWeather() {
                        this.error = ''
                        this.weather = {}
                        if (this.city === '') {
                            return;
                        }
                        this.loading = true
                        fetch('{{ env('APP_URL') }}/api/weather/' + this.city)
                            .then((res) => res.json())
                            .then((res) => {
                                if (!res.temperature_2m) {
                                    this.error = 'Error happened when fetching the API'
                                } else {
                                    this.weather = res
                                }
                                this.loading = false
                            })
                    }
                }
            }
    </script>
    @endsection
</x-app-layout>