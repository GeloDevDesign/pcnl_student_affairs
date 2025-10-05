<script setup>
import { ref, onMounted } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
    election: Object,
    results: Array,
    totalVoters: Number,
    canViewResults: Boolean,
});

const chartRef = ref(null);

// Format date helper
function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

// Initialize single chart for all candidates
onMounted(() => {
    if (!props.results || !props.canViewResults || !chartRef.value) return;

    const ctx = chartRef.value.getContext('2d');
    
    // Flatten all candidates from all roles
    const allCandidates = [];
    const labels = [];
    const data = [];
    const colors = [];
    const borderColors = [];
    
    props.results.forEach((role) => {
        role.candidates.forEach((candidate, index) => {
            labels.push(`${candidate.full_name}\n(${role.role_name})`);
            data.push(candidate.votes);
            
            // Green for winners (first in each role), blue for others
            const isWinner = index === 0;
            colors.push(isWinner ? 'rgba(34, 197, 94, 0.8)' : 'rgba(59, 130, 246, 0.8)');
            borderColors.push(isWinner ? 'rgb(34, 197, 94)' : 'rgb(59, 130, 246)');
            
            allCandidates.push({ ...candidate, role: role.role_name, isWinner });
        });
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Votes',
                data: data,
                backgroundColor: colors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 6,
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
                    callbacks: {
                        label: (context) => {
                            const candidate = allCandidates[context.dataIndex];
                            return [
                                `Votes: ${context.parsed.y}`,
                                `Party: ${candidate.party_list}`,
                                candidate.isWinner ? '🏆 Winner' : ''
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
                    },
                    title: {
                        display: true,
                        text: 'Votes'
                    }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
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
        <div v-else class="bg-white p-6 shadow-sm rounded-lg border border-gray-100">
            <!-- Header -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ election?.name }}</h3>
                <p class="text-sm text-gray-500">
                    {{ formatDate(election?.start_date) }} - {{ formatDate(election?.end_date) }}
                </p>
                <p class="text-sm text-gray-600 mt-2">Total Voters: {{ totalVoters }}</p>
            </div>

            <!-- Single Chart -->
            <div class="h-96 mb-4">
                <canvas ref="chartRef"></canvas>
            </div>

            <!-- Legend -->
            <div class="flex items-center justify-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-gray-700">Winners</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span class="text-gray-700">Other Candidates</span>
                </div>
            </div>
        </div>
    </div>
</template>