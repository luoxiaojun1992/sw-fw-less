#include <x86intrin.h>

double ArraySum(double numbers[], int size);

double ArraySum(double numbers[], int size) {
    double sum = 0;
    for (int i = 0; i < size; i++) {
        sum += numbers[i];
    }
    return sum;
}

double ArrayAvg(double numbers[], int size) {
    return ArraySum(numbers, size) / size;
}

double ArrayMin(double numbers[], int size) {
    double min = 0;
    for (int i = 0; i < size; i++) {
        if (numbers[i] < min) {
            min = numbers[i];
        }
    }
    return min;
}

double ArrayMax(double numbers[], int size) {
    double max = 0;
    for (int i = 0; i < size; i++) {
        if (numbers[i] > max) {
            max = numbers[i];
        }
    }
    return max;
}

double Add(double number1, double number2) {
    return number1 + number2;
}

float* vectorAdd(float vector1[], float vector2[], float sum[]) {
    __m128 mVector1;
    __m128 mVector2;
    __m128 mSum;

    mVector1 = _mm_loadu_ps(vector1);
    mVector2 = _mm_loadu_ps(vector2);

    mSum = _mm_add_ps(mVector1, mVector2);

    _mm_storeu_ps(sum, mSum);

    return sum;
}
