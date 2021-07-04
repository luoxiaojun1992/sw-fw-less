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

void VectorSub(float vector1[], float vector2[], int size, float result[]){
    __m128 mVector1;
    __m128 mVector2;
    __m128 mDiff;

    float op1[size];
    float op2[size];
    float diff[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
        op2[i] = vector2[i];
    }

    mVector1 = _mm_loadu_ps(op1);
    mVector2 = _mm_loadu_ps(op2);

    mDiff = _mm_sub_ps(mVector1, mVector2);

    _mm_storeu_ps(diff, mDiff);

    for (i = 0; i < size; ++i) {
        result[i] = diff[i];
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

void VectorDiv(float vector1[], float vector2[], int size, float result[]){
    __m128 mVector1;
    __m128 mVector2;
    __m128 mDiv;

    float op1[size];
    float op2[size];
    float div[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
        op2[i] = vector2[i];
    }

    mVector1 = _mm_loadu_ps(op1);
    mVector2 = _mm_loadu_ps(op2);

    mDiv = _mm_div_ps(mVector1, mVector2);

    _mm_storeu_ps(div, mDiv);

    for (i = 0; i < size; ++i) {
        result[i] = div[i];
    }
}

void VectorSqrt(float vector1[], int size, float result[]) {
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

void VectorCmp(float vector1[], float vector2[], int size, float result[]){
    __m128 mVector1;
    __m128 mVector2;
    __m128 mRes;

    float op1[size];
    float op2[size];
    float res[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
        op2[i] = vector2[i];
    }

    mVector1 = _mm_loadu_ps(op1);
    mVector2 = _mm_loadu_ps(op2);

    mRes = _mm_cmpge_ps(mVector1, mVector2);

    _mm_storeu_ps(res, mRes);

    for (i = 0; i < size; ++i) {
        result[i] = res[i];
    }
}

void VectorRcp(float vector1[], int size, float result[]) {
    __m128 mVector1;
    __m128 mRcp;

    float op1[size];
    float rcp[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
    }

    mVector1 = _mm_loadu_ps(op1);

    mRcp = _mm_rcp_ps(mVector1);

    _mm_storeu_ps(rcp, mRcp);

    for (i = 0; i < size; ++i) {
        result[i] = rcp[i];
    }
}

void VectorAbs(int vector1[], int size, int result[]) {
    __m128i mVector1;
    __m128i mAbs;

    int op1[size];
    int absRes[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
    }

    mVector1 = _mm_loadu_si128((__m128i *)op1);

    mAbs = _mm_abs_epi32(mVector1);

    _mm_storeu_si128((__m128i *)absRes, mAbs);

    for (i = 0; i < size; ++i) {
        result[i] = absRes[i];
    }
}

void VectorCeil(float vector1[], int size, float result[]) {
    __m128 mVector1;
    __m128 mCeil;

    float op1[size];
    float ceilRes[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
    }

    mVector1 = _mm_loadu_ps(op1);

    mCeil = _mm_ceil_ps(mVector1);

    _mm_storeu_ps(ceilRes, mCeil);

    for (i = 0; i < size; ++i) {
        result[i] = ceilRes[i];
    }
}

void VectorFloor(float vector1[], int size, float result[]) {
    __m128 mVector1;
    __m128 mFloor;

    float op1[size];
    float floorRes[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
    }

    mVector1 = _mm_loadu_ps(op1);

    mFloor = _mm_floor_ps(mVector1);

    _mm_storeu_ps(floorRes, mFloor);

    for (i = 0; i < size; ++i) {
        result[i] = floorRes[i];
    }
}

void VectorRound(float vector1[], int size, float result[]) {
    __m128 mVector1;
    __m128 mRound;

    float op1[size];
    float roundRes[size];

    int i = 0;
    for (i = 0; i < size; ++i) {
        op1[i] = vector1[i];
    }

    mVector1 = _mm_loadu_ps(op1);

    mRound = _mm_round_ps(mVector1, (_MM_FROUND_TO_NEAREST_INT |_MM_FROUND_NO_EXC));

    _mm_storeu_ps(roundRes, mRound);

    for (i = 0; i < size; ++i) {
        result[i] = roundRes[i];
    }
}
