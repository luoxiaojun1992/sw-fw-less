#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include "include/genann/genann.c"

int TrainAndPredictOnce(const double** input, const double** output, const int sample_total, const double* test_input, double* test_output, const int inputs, const int hidden_layers, const int hidden, const int outputs, const double learning_rate, const int epochs) {
    srand(time(0));

    genann *ann = genann_init(inputs, hidden_layers, hidden, outputs);

    int i, j;
    for (i = 0; i < epochs; ++i) {
        for (j = 0; j < sample_total; ++j) {
            genann_train(ann, *(input + j), *(output + j), learning_rate);
        }
    }

    *test_output = *genann_run(ann, test_input);

    genann_free(ann);

    return 0;
}

//demo
//int main(int argc, char *argv[])
//{
//    int i;
//
//    const double input[4][2] = {{0, 0}, {0, 1}, {1, 0}, {1, 1}};
//    const double* inputPointer[4];
//    for (i = 0; i < 4; ++i) {
//        inputPointer[i] = &(input[i]);
//    }
//
//    const double output[4][1] = {{0}, {1}, {1}, {0}};
//    const double* outputPointer[4];
//    for (i = 0; i < 4; ++i) {
//        outputPointer[i] = &(output[i]);
//    }
//
//    const double test_input[2] = {0, 1};
//    double test_output[1];
//
//    TrainAndPredictOnce(inputPointer, outputPointer, 4, test_input, test_output, 2, 1, 2, 1, 3, 500);
//
//    printf("%.1f", test_output[0]);
//
//    return 0;
//}
