<script setup>
import { ref, onMounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
    election: Object,
    results: Array,
    totalVoters: Number,
    canViewResults: Boolean,
});

const chartRefs = ref([]);

// Format date helper
function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

// Initialize charts for each role
onMounted(async () => {
    if (!props.results || !props.canViewResults) return;

    await nextTick();

    props.results.forEach((role, roleIndex) => {
        const canvas = chartRefs.value[roleIndex];
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        const labels = role.candidates.map(c => c.full_name);
        const data = role.candidates.map(c => c.votes);
        
        // Color the winner (first candidate) green, others blue
        const colors = role.candidates.map((_, index) => 
            index === 0 ? 'rgba(34, 197, 94, 0.7)' : 'rgba(59, 130, 246, 0.7)'
        );
        const borderColors = role.candidates.map((_, index) => 
            index === 0 ? 'rgb(34, 197, 94)' : 'rgb(59, 130, 246)'
        );

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Votes',
                    data: data,
                    backgroundColor: colors,
                    borderColor: borderColors,
                    borderWidth: 1,
                    borderRadius: 4,
                    barThickness: 'flex',
                    maxBarThickness: 40,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        cornerRadius: 6,
                        titleFont: {
                            size: 13,
                            weight: 'normal'
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: (context) => {
                                const candidate = role.candidates[context.dataIndex];
                                return [
                                    `Votes: ${context.parsed.y}`,
                                    `Party: ${candidate.party_list}`,
                                    context.dataIndex === 0 ? '🏆 Winner' : ''
                                ].filter(Boolean);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    });
});
</script>

<template>
    <div class="mt-6">
        <!-- No Results Available -->
        <div v-if="!canViewResults || !election" class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Results Not Available</h2>
            <p class="text-gray-600 text-sm">Results will be shown after voting ends.</p>
        </div>

        <!-- Results Available -->
        <div v-else class="space-y-5">
            <!-- Header -->
            <div class="bg-white p-5 shadow-sm rounded-lg border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ election?.name }}</h3>
                <p class="text-sm text-gray-500">
                    {{ formatDate(election?.start_date) }} - {{ formatDate(election?.end_date) }}
                </p>
                <p class="text-sm text-gray-600 mt-2">Total Voters: {{ totalVoters }}</p>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div 
                    v-for="(role, index) in results" 
                    :key="role.role_id"
                    class="bg-white p-5 shadow-sm rounded-lg border border-gray-200"
                >
                    <div class="mb-4">
                        <h4 class="text-base font-semibold text-gray-800">{{ role.role_name }}</h4>
                    </div>
                    
                    <div class="h-64 mb-3">
                        <canvas :ref="el => chartRefs[index] = el"></canvas>
                    </div>

                    <!-- Role-specific Legend -->
                    <div class="flex items-center justify-center gap-4 text-xs pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 bg-green-500 rounded"></div>
                            <span class="text-gray-600">Winner</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 bg-blue-500 rounded"></div>
                            <span class="text-gray-600">Others</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>