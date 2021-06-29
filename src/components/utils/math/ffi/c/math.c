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

void VectorAdd(float vector1[], float vector2[], int size, float result[]){
    __m128 mVector1;
    __m128 mVector2;
    __m128 mSum;

    float op1[size];
    float op2[size];
    float sum[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
        op2[i] = vector2[i];
    }

    mVector1 = _mm_loadu_ps(op1);
    mVector2 = _mm_loadu_ps(op2);

    mSum = _mm_add_ps(mVector1, mVector2);

    _mm_storeu_ps(sum, mSum);

    for (i = 0; i < size; ++i) {
        result[i] = sum[i];
    }
}

void VectorMul(float vector1[], float vector2[], int size, float result[]){
    __m128 mVector1;
    __m128 mVector2;
    __m128 mProduct;

    float op1[size];
    float op2[size];
    float product[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
        op2[i] = vector2[i];
    }

    mVector1 = _mm_loadu_ps(op1);
    mVector2 = _mm_loadu_ps(op2);

    mProduct = _mm_mul_ps(mVector1, mVector2);

    _mm_storeu_ps(product, mProduct);

    for (i = 0; i < size; ++i) {
        result[i] = product[i];
    }
}

void vectorSqrt(float vector1[], float vector2[], int size, float result[]) {
    __m128 mVector1;
    __m128 mRoot;

    float op1[size];
    float root[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
    }

    mVector1 = _mm_loadu_ps(op1);

    mRoot = _mm_sqrt_ps(mVector1);

    _mm_storeu_ps(root, mRoot);

    for (i = 0; i < size; ++i) {
        result[i] = root[i];
    }
}
