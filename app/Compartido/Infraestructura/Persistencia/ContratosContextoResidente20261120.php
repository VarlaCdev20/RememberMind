<?php

declare(strict_types=1);

namespace App\Compartido\Infraestructura\Persistencia;

use Illuminate\Database\Connection;

/** Contrato SQL versionado del sujeto original, contrastado sin leer filas clínicas. */
final class ContratosContextoResidente20261120
{
    /** Huellas de guardas instaladas y ejecutadas en ambos motores; sin datos de residentes. */
    private const HUELLAS = [
        'sqlite' => [
            'mediciones_clinicas' => [
                'ctx20_3bd24fbcfd16_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '69cae73890894987c1d49c5fb13ab34e493c5c1288a15125936d7f3d5095865e',
                ],
                'ctx20_3bd24fbcfd16_directo_delete' => [
                    'tabla' => 'residentes_mediciones_clinicas',
                    'hash' => '7861e83ea2e26fb0b1b2ee0ee199c619870c623cfc86b6cb9ea478ee7f04a6b2',
                ],
                'ctx20_3bd24fbcfd16_directo_insert' => [
                    'tabla' => 'residentes_mediciones_clinicas',
                    'hash' => '1fd363892727808a7570f4e6af4f4a102266ffdc8e736af96d2867575c71bb34',
                ],
                'ctx20_3bd24fbcfd16_directo_update' => [
                    'tabla' => 'residentes_mediciones_clinicas',
                    'hash' => 'd7747de5d943b004d464ef1b29913ae465af3852ce4121fba788fa1713efdd78',
                ],
                'ctx20_3bd24fbcfd16_episodio' => [
                    'tabla' => 'mediciones_clinicas',
                    'hash' => '5f60e8c292984b3b523a10725fa5fb1424cbc0eb9244d8e479a0e6f16adcf54f',
                ],
                'ctx20_3bd24fbcfd16_insert' => [
                    'tabla' => 'mediciones_clinicas',
                    'hash' => '675b20d3cd0108f9cc55fb3d7212b8df18b6655d83a9e70f006cacc9f90bf44c',
                ],
                'ctx20_3bd24fbcfd16_update' => [
                    'tabla' => 'mediciones_clinicas',
                    'hash' => '2b27ccc297050f08a1e18e9751019625a6fa324064e2d9c1772090dff24ae856',
                ],
            ],
            'registros_observaciones_cuidado' => [
                'ctx20_46f568014f12_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '59cb265166d07be0e3342cc0ce34eb500b45a59a99419781146b86ac2ec30ea1',
                ],
                'ctx20_46f568014f12_directo_delete' => [
                    'tabla' => 'residentes_observaciones_cuidado',
                    'hash' => '9b36e7f2769755fc5150a23b82d7ce9d5dfaba9f836fe8817ac740797bc02468',
                ],
                'ctx20_46f568014f12_directo_insert' => [
                    'tabla' => 'residentes_observaciones_cuidado',
                    'hash' => 'ce4d01f381790b90a340846faf54d4024a3238fc84012dc06702f7014eaaca39',
                ],
                'ctx20_46f568014f12_directo_update' => [
                    'tabla' => 'residentes_observaciones_cuidado',
                    'hash' => 'eafc7a10530b33eeadfe9524209933bca4d90f1cc4d48f5c121d39f360f67221',
                ],
                'ctx20_46f568014f12_episodio' => [
                    'tabla' => 'registros_observaciones_cuidado',
                    'hash' => 'de4fa9f11514c2609ab463d8a82b07a1e42c67c88b1c443005038191c28b90cd',
                ],
                'ctx20_46f568014f12_insert' => [
                    'tabla' => 'registros_observaciones_cuidado',
                    'hash' => '78254c1065789b9e33712dfee494edbc7f68e14429dd0c74a450100e9a6c7bc5',
                ],
                'ctx20_46f568014f12_update' => [
                    'tabla' => 'registros_observaciones_cuidado',
                    'hash' => '20f5dc60f5e1fa4eddd1ecd88dc92d23c5d3c93bb75e0fb4671afad30a53ee15',
                ],
            ],
            'aplicaciones_instrumento' => [
                'ctx20_526dcbb8bee3_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '3b1be462efbf8062b3a59e7dc15ab1cd8d018c07f5cb3fa00374e06a7af6179e',
                ],
                'ctx20_526dcbb8bee3_directo_delete' => [
                    'tabla' => 'residentes_aplicaciones_instrumento',
                    'hash' => 'e1fdff67e8a0979738fdf0b72160212dfea260d0f2db8e32d9b562c180885adf',
                ],
                'ctx20_526dcbb8bee3_directo_insert' => [
                    'tabla' => 'residentes_aplicaciones_instrumento',
                    'hash' => '0b9b1ad510e3dd920791535e90ee25bc027c90f5f73a0dc18fb8924be5624a89',
                ],
                'ctx20_526dcbb8bee3_directo_update' => [
                    'tabla' => 'residentes_aplicaciones_instrumento',
                    'hash' => '7161ce88d3cbcf0dca11e8a285ab329c631fac02c9a71787f69e5f9a628f0e17',
                ],
                'ctx20_526dcbb8bee3_episodio' => [
                    'tabla' => 'aplicaciones_instrumento',
                    'hash' => 'f9f8cfdce5e059758fee5796ac1c696507d0fce0e713c879110e2b1330875a91',
                ],
                'ctx20_526dcbb8bee3_insert' => [
                    'tabla' => 'aplicaciones_instrumento',
                    'hash' => '9bdeee97ab95eaefb2d5d1ad07df155b964d4e785183c0fad42669013dafdfd3',
                ],
                'ctx20_526dcbb8bee3_update' => [
                    'tabla' => 'aplicaciones_instrumento',
                    'hash' => '240866c57ba16104ed528e2917cdbf0b7cc9a7ce9d8c7e271cf8be9db7d3285f',
                ],
            ],
            'controles_cognitivos' => [
                'ctx20_a5e2c4edb9a6_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '853398cb5192c07ed9eb1a3bc9591bf3521bb7697c205e86de24dc0670db1003',
                ],
                'ctx20_a5e2c4edb9a6_directo_delete' => [
                    'tabla' => 'residentes_controles_cognitivos',
                    'hash' => 'd2c9c2e092211b98d6ac1ca4bb296400d5640a23a84597d02d79bfa2e39537cd',
                ],
                'ctx20_a5e2c4edb9a6_directo_insert' => [
                    'tabla' => 'residentes_controles_cognitivos',
                    'hash' => '39e1841b8a5efbfb021fd24256bc22543bbaa25533d87b1d8b19d11824f45875',
                ],
                'ctx20_a5e2c4edb9a6_directo_update' => [
                    'tabla' => 'residentes_controles_cognitivos',
                    'hash' => '4fa8bc988d9d7b6fd45eef1119b6cf368c2f3b95a2813417b2720fa1a267e86a',
                ],
                'ctx20_a5e2c4edb9a6_episodio' => [
                    'tabla' => 'controles_cognitivos',
                    'hash' => '81a5d71f13f280faf2150ca6f0bf3fc4bdbd35e897d3594b1be063959a8784b2',
                ],
                'ctx20_a5e2c4edb9a6_insert' => [
                    'tabla' => 'controles_cognitivos',
                    'hash' => '88a5c547cf14b9115ab1ac4a74138ac3ab9578f13be45cdda3f90385195ec3f5',
                ],
                'ctx20_a5e2c4edb9a6_update' => [
                    'tabla' => 'controles_cognitivos',
                    'hash' => '0aab95ad955b94bcd99a2f2e4ef93c56a3ad61952ee855d9b5794c710ad39036',
                ],
            ],
            'controles_funcionales_iadl' => [
                'ctx20_ef823100c702_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '55618a9f17ce6fba293b0750d63214bef5e4c6d1faa25ce6e2b305d3d9bc2505',
                ],
                'ctx20_ef823100c702_directo_delete' => [
                    'tabla' => 'residentes_controles_funcionales_iadl',
                    'hash' => 'ef5aa130c982e1d400d0440d43df6e337fc90ba0994a6cbda03132b3927dce7d',
                ],
                'ctx20_ef823100c702_directo_insert' => [
                    'tabla' => 'residentes_controles_funcionales_iadl',
                    'hash' => '96c557bdb3bb31831161999a529dd3e4642ae0e2089ab3f2cc499a815bbdcd65',
                ],
                'ctx20_ef823100c702_directo_update' => [
                    'tabla' => 'residentes_controles_funcionales_iadl',
                    'hash' => '1c038d6c9898ae7df423e0491e1a4b97a0a592604f1da392409a9b93240b394b',
                ],
                'ctx20_ef823100c702_episodio' => [
                    'tabla' => 'controles_funcionales_iadl',
                    'hash' => '385a2ec87ee1390d4b073538dd79ceed494e3d33585841d2e352ee993b9a033c',
                ],
                'ctx20_ef823100c702_insert' => [
                    'tabla' => 'controles_funcionales_iadl',
                    'hash' => 'c0d22acf251a17efe0d0211138c16664e9eea73483cf1d1997397f56084706c5',
                ],
                'ctx20_ef823100c702_update' => [
                    'tabla' => 'controles_funcionales_iadl',
                    'hash' => '0b48bf84643231db7ccc8d96c6dc73975c5342478062059a43562759625676e1',
                ],
            ],
        ],
        'pgsql' => [
            'mediciones_clinicas' => [
                'ctx20_3bd24fbcfd16' => [
                    'tabla' => 'mediciones_clinicas',
                    'hash' => '39272d949efb903b7c978478d9a895754222a2210d7d0fe8b4215bc9a8eecee5',
                    'tipo' => 23,
                    'enlace' => '7da36da44e7d63245398bd87082b3ceb91349a628d54043651c662b4e4d8d28b',
                ],
                'ctx20_3bd24fbcfd16_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => 'a2b203ae2906b0cd89f7e60b7dbd253dbeeaf81970f7689535ddbfe3fa032175',
                    'tipo' => 19,
                    'enlace' => '432e6802ab8f51bc695444f4110b802ecafec0062b134251ca150df8dd229286',
                ],
                'ctx20_3bd24fbcfd16_directo' => [
                    'tabla' => 'residentes_mediciones_clinicas',
                    'hash' => '36bba89ffd587d76d8d4b6a27b54f3639075e4d4cac51a37893d36939ffaf507',
                    'tipo' => 31,
                    'enlace' => '538d5da1babc63034cf89c021b658fa927337fda05e5dfe33e09cb5d438c97e7',
                ],
            ],
            'registros_observaciones_cuidado' => [
                'ctx20_46f568014f12' => [
                    'tabla' => 'registros_observaciones_cuidado',
                    'hash' => '06de19f74435d2eb244b89897daf738cf67170513f7edcd66845784c625e451c',
                    'tipo' => 23,
                    'enlace' => 'e7128402b094e386fdd5510f94ddfb2ba83b6936e5643fcd14bf4a4f1529a379',
                ],
                'ctx20_46f568014f12_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '2c9510f4bffa3503cc69e2022362f756fd04b2ee7e9f149ee667d5ecd44a83ff',
                    'tipo' => 19,
                    'enlace' => '1a2a080ebe2b4ec3c3ed468f1633eaa0d1581c6af9be7c771fd0b311917b2cd2',
                ],
                'ctx20_46f568014f12_directo' => [
                    'tabla' => 'residentes_observaciones_cuidado',
                    'hash' => '67b961339c79433941635a59fe8a1d6b258bab5309a510da06d506ef078a85bd',
                    'tipo' => 31,
                    'enlace' => 'd196db6484e55328c77b07c3bda7e266dbcf7d9df39ae964e64d0517af810a1b',
                ],
            ],
            'aplicaciones_instrumento' => [
                'ctx20_526dcbb8bee3' => [
                    'tabla' => 'aplicaciones_instrumento',
                    'hash' => 'ea0317ec0a8e2cf1ad22003e6e8a16fdfd078afafe3215a9f8583ebf521b6794',
                    'tipo' => 23,
                    'enlace' => '08165c6c166b09098138431accfb4e89e232e9c7b9c5a7fad97454aff306c8c8',
                ],
                'ctx20_526dcbb8bee3_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '140eeec6fd92ee3d00dd7edd39ad5dbec8846160654af219eca00e007c28653b',
                    'tipo' => 19,
                    'enlace' => '14617d61000a3f0f0440cc1732dea6b608dd85b7c3e441690ac01409d4ed0f7f',
                ],
                'ctx20_526dcbb8bee3_directo' => [
                    'tabla' => 'residentes_aplicaciones_instrumento',
                    'hash' => '423aefb0b065118235bc85ae2aef231adc0b658e9225bc78d5184bdbf81349df',
                    'tipo' => 31,
                    'enlace' => 'bafc0bf8f9cca8cf187ec9f47df4807f4a10f50702474b13b240babce1472aee',
                ],
            ],
            'controles_cognitivos' => [
                'ctx20_a5e2c4edb9a6' => [
                    'tabla' => 'controles_cognitivos',
                    'hash' => '1a4f7dffc4e7831e5c38dbe1c9844de0ab9b693d28d6b2db1dfc5cf0d81e482d',
                    'tipo' => 23,
                    'enlace' => '74adecbed89b9f1239743e0300b59d198794db2502889b74db9b3da6f48c0a59',
                ],
                'ctx20_a5e2c4edb9a6_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '8bc685b07fc8b61e805aa0cf192a3c32dbd6f5e0491e2223d566acdbc9708d8f',
                    'tipo' => 19,
                    'enlace' => '85ec67afd7839a0cc66083d33523afd204f965d06ed867a0de58e65f05074a37',
                ],
                'ctx20_a5e2c4edb9a6_directo' => [
                    'tabla' => 'residentes_controles_cognitivos',
                    'hash' => 'f2e814f14b935ff1faa30464402886d4699e7769e5ff34fa4e334279df3fb710',
                    'tipo' => 31,
                    'enlace' => 'f9edf47ca4aabece2479db3a68954d2f591fe401aa7e8e4b3482d42e64498ae4',
                ],
            ],
            'controles_funcionales_iadl' => [
                'ctx20_ef823100c702' => [
                    'tabla' => 'controles_funcionales_iadl',
                    'hash' => '07dd09d41fe7938abd2d4b03dcdf1006dda8014bd99a6deee337f7d3c1fdf32c',
                    'tipo' => 23,
                    'enlace' => '2135a600457559a8f46feababb338381df66c0d4ffd066abc9752cb39b48ae62',
                ],
                'ctx20_ef823100c702_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '134d5a978f2c12380660b0aecfa345c4e0ae1f338f197ffec41113f2828817bf',
                    'tipo' => 19,
                    'enlace' => '53ccb55773e6ec7fd0dbf26bc70d705b8f32b082048bc27e05eaa92442645a19',
                ],
                'ctx20_ef823100c702_directo' => [
                    'tabla' => 'residentes_controles_funcionales_iadl',
                    'hash' => 'c3118667ab9279f72cc7eb856cc93eacaaec7697583c250e21efe7c1ba697c0e',
                    'tipo' => 31,
                    'enlace' => '29f98988278466c572909faf7147919dcece8e258a578826c49b684346df7859',
                ],
            ],
        ],
    ];

    public static function aliasCanonico(?array $generacion, string $pk): bool
    {
        if (($generacion['type'] ?? null) !== 'stored') {
            return false;
        }
        $expresion = preg_replace('/::(?:character\s+varying|varchar|text)\b(?!\s*\()/i', '', $generacion['expression'] ?? '');
        $expresion = preg_replace('/[\s"()]+/u', '', strtolower($expresion));

        return $expresion === 'casewhencod_atencionisnullthen'.$pk.'elsenullend';
    }

    public static function guardasInstaladas(Connection $db, string $tabla): bool
    {
        $motor = $db->getDriverName();
        $esperadas = self::HUELLAS[$motor][$tabla] ?? null;
        if ($esperadas === null) {
            return false;
        }
        if ($motor === 'sqlite') {
            $actuales = $db->table('sqlite_master')->where('type', 'trigger')->whereIn('name', array_keys($esperadas))->get(['name', 'tbl_name', 'sql'])->keyBy('name');
            if ($actuales->count() !== count($esperadas)) {
                return false;
            }
            foreach ($esperadas as $nombre => $e) {
                $a = $actuales->get($nombre);
                if ($a === null || $a->tbl_name !== $e['tabla'] || ! hash_equals($e['hash'], self::huella($a->sql))) {
                    return false;
                }
            }

            return true;
        }
        $nombres = implode(',', array_fill(0, count($esperadas), '?'));
        $actuales = collect($db->select('SELECT t.tgname, c.relname AS tabla, t.tgtype, t.tgenabled, pg_get_expr(t.tgqual,t.tgrelid) AS condicion, pg_get_triggerdef(t.oid) AS enlace, pg_get_functiondef(t.tgfoid) AS definicion FROM pg_trigger t JOIN pg_class c ON c.oid=t.tgrelid JOIN pg_namespace n ON n.oid=c.relnamespace WHERE n.nspname=\'public\' AND NOT t.tgisinternal AND t.tgname IN ('.$nombres.')', array_keys($esperadas)));
        if ($actuales->count() !== count($esperadas)) {
            return false;
        }
        foreach ($actuales as $a) {
            $e = $esperadas[$a->tgname];
            if ($a->tabla !== $e['tabla'] || (int) $a->tgtype !== $e['tipo'] || ! in_array($a->tgenabled, ['O', 'A'], true)
                || $a->condicion !== null || ! hash_equals($e['hash'], self::huella($a->definicion)) || ! hash_equals($e['enlace'], self::huella($a->enlace))) {
                return false;
            }
        }

        return true;
    }

    private static function huella(string $sql): string
    {
        return hash('sha256', trim(str_replace(["\r\n", "\r"], ["\n", "\n"], $sql)));
    }
}
