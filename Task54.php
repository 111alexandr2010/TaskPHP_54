<?php
header('Content-Type: text/html;charset=utf-8');

$handle = fopen('users.csv', 'rb');
/**
 *    будем использовать основные массивы:
 * 1)список отделов, $departments = [];
 * 2)список счастливчиков, $luckyPerson = [0 => [], 1 => [], 2 => [], 3 => [], 4 => []];
 * 3)список несчастливчиков, $unLuckyPerson = [0 => [], 1 => [], 2 => [], 3 => [], 4 => []];
 * 4)количество сотрудников в отделах, $depEmployeeCount = [];
 * 5)суммарный возраст по отделам, $depSumAge = [];
 * 6)сумма зарплаты по отделам, $depSumSalary = [];
 * 7)максимальная зарплата в каждом отделе, $salaryMax = [];
 * 8)минимальная зарплата в каждом отделе, $salaryMin = [];
 */

if ($handle) {
    $i = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $i++;
        /**
         * создаем вспомогательный двумерный массив $arrayData и помещаем в него данные из файла,
         * далее массив сортируем по второму индексу(департамент) с
         * помощью функции "usort" и пользовательской функции "compare",
         * которая определяет очередность своих параметров
         */
        $arrayData[$i] = $data;
    }

    function compare($a, $b)
    {
        if ($a[1] == $b[1]) return 0;
        return ($a[1] < $b[1]) ? -1 : 1;
    }

    /**
     * Проверяем, если в массиве нет элементов - выводим сообщение,
     * иначе присваиваем первому элементу каждого основного массива
     * соответствующее значение первого элемента вспомогательного массива
     */
    if (count($arrayData) == 0) {
        echo 'Нет данных!';
    } else {
        usort($arrayData, "compare");

        $index = 1;
        $departments[$index] = $arrayData[$index][1];
        $depEmployeeCount [$index] = 1;
        $depSumAge [$index] = $arrayData[$index][2];
        $depSumSalary [$index] = $arrayData[$index][3];
        $salaryMax [$index] = $arrayData[$index][3];
        $salaryMin [$index] = $arrayData[$index][3];
        $luckyPerson[$index][0] = $arrayData[$index][0];
        $unLuckyPerson[$index][0] = $arrayData[$index][0];
        /**
         * Проходим по вспомогательному массиву в цикле со 2-го элемента,
         * проверяя совпадение текущего и предыдущего элемента по 2-му индексу(отдел).
         * Если отдел не изменился - увеличиваем количество сотрудников, суммарный возраст,
         * сумму зарплаты. Также сравниваем максимальное и минимальное значение зарплаты отдела,
         * если текущее значение совпадает, то добавляем новое имя в массив, если текущее значение
         * больше максимального (или соответственно меньше минимального), то обнуляем массивы счастливчиков
         * и несчастливчиков и присваиваем певому элементу текущее значение зарплаты.         *
         */
        for ($i = 2; $i < count($arrayData); $i++)
            if ($arrayData[$i - 1][1] !== $arrayData[$i][1]) {
                $index++;
                $departments[$index] = $arrayData[$i][1];
                $depEmployeeCount [$index] = 1;
                $depSumAge [$index] = $arrayData[$i][2];
                $depSumSalary [$index] = $arrayData[$i][3];
                $salaryMax [$index] = $arrayData[$i][3];
                $salaryMin [$index] = $arrayData[$i][3];
                $luckyPerson[$index][0] = $arrayData[$i][0];
                $unLuckyPerson[$index][0] = $arrayData[$i][0];
            } else {
                $depEmployeeCount[$index]++;
                $depSumAge[$index] += $arrayData[$i][2];
                $depSumSalary[$index] += $arrayData[$i][3];

                if ($salaryMax[$index] < $arrayData[$i][3]) {
                    unset($luckyPerson[$index]);
                    $luckyPerson[$index][0] = $arrayData[$i][0];
                    $salaryMax[$index] = $arrayData[$i][3];
                } elseif ($salaryMax[$index] == $arrayData[$i][3]) {
                    $luckyPerson[$index][] = $arrayData[$i][0];
                }

                if ($salaryMin[$index] > $arrayData[$i][3]) {
                    unset($unLuckyPerson[$index]);
                    $unLuckyPerson[$index][0] = $arrayData[$i][0];
                    $salaryMin[$index] = $arrayData[$i][3];
                } elseif ($salaryMin[$index] == $arrayData[$i][3]) {
                    $unLuckyPerson[$index][] = $arrayData[$i][0];
                }
            }
    }
}
fclose($handle);
/**
 * Выводим статистику по отделам
 */

for ($i = 1; $i < count($departments); $i++) {
    if ($depEmployeeCount[$i] > 0) {
        echo '________Статистика по отделу "' . ($departments[$i]) . '" : ' . '<br>';
        echo 'Количество сотрудников : ' . number_format($depEmployeeCount[$i], 0, ',', ' ') . '<br>';
        echo 'Средняя ЗП : ' . number_format($depSumSalary[$i] / $depEmployeeCount[$i], 2, ',', ' ') . '<br>';
        echo 'Средний возраст: ' . number_format($depSumAge[$i] / $depEmployeeCount[$i], 2) . '<br>';
        echo '______Счастливчики с ЗП ' . number_format($salaryMax[$i], 0, ',', ' ') . ':' . '<br>';
        for ($j = 0; $j < count($luckyPerson[$i]); $j++) {
            echo $luckyPerson[$i][$j] . ',' . '<br>';
        }
        echo '______Несчастные с ЗП ' . number_format($salaryMin[$i], 0, ',', ' ') . ':' . '<br>';
        for ($j = 0; $j < count($unLuckyPerson[$i]); $j++) {
            echo $unLuckyPerson[$i][$j] . ',' . '<br>';
        }
    } else {
        echo 'В отделе  "' . ($departments[$i]) . '"  нет сотрудников' . '<br>';
    }
}
echo '<br>';
/**
 * Выводим списки несчастливчиков и счастливчиков всей компании
 */
echo '_____СПИСОК НЕСЧАСТЛИВЧИКОВ КОМПАНИИ:' . '<br>';
for ($i = 1; $i < count($departments); $i++) {
    for ($j = 0; $j < count($unLuckyPerson[$i]); $j++) {
        echo $unLuckyPerson[$i][$j] . ' ("' . $departments[$i] . '") с зарплатой ' . $salaryMin[$i];
        echo '<br>';
    }
}
echo '<br>';

echo '_____СПИСОК СЧАСТЛИВЧИКОВ КОМПАНИИ:' . '<br>';
for ($i = 1; $i < count($departments); $i++) {
    for ($j = 0; $j < count($luckyPerson[$i]); $j++) {
        echo $luckyPerson[$i][$j] . ' ("' . $departments[$i] . '") с зарплатой ' . $salaryMax[$i];
        echo '<br>';
    }
};

