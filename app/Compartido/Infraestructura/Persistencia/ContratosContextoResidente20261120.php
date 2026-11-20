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
            'derivaciones' => [
                'ctx20_f889ca2ee034_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '50dced0a15a8d24ae14cab6b0aec49bfa2b6cf536a9ca41ea7b908a07c9f67a6',
                ],
                'ctx20_f889ca2ee034_directo_delete' => [
                    'tabla' => 'residentes_derivaciones',
                    'hash' => '9557f8fc3f0bf3ac00b3ce80185999a8ced466db2b0969916b1a3113f93568eb',
                ],
                'ctx20_f889ca2ee034_directo_insert' => [
                    'tabla' => 'residentes_derivaciones',
                    'hash' => '8e8078e9dd0dd22f620d3ac8b504d25d38dd3d88d0f0944ac3b0318962e900d6',
                ],
                'ctx20_f889ca2ee034_directo_update' => [
                    'tabla' => 'residentes_derivaciones',
                    'hash' => 'a4b48398b204c8b6e842ed80820fecfca9cb44c831804e9c5689bbd891d598d5',
                ],
                'ctx20_f889ca2ee034_episodio' => [
                    'tabla' => 'derivaciones',
                    'hash' => '48bfd7e226784c89a1b39d020cc4312464c57c29af996e4e876069e67c8d3512',
                ],
                'ctx20_f889ca2ee034_insert' => [
                    'tabla' => 'derivaciones',
                    'hash' => 'b238bc8741be32bd018b3e8b0943a79b6f6f6a94c0517036b1a95895a15e44bb',
                ],
                'ctx20_f889ca2ee034_update' => [
                    'tabla' => 'derivaciones',
                    'hash' => 'df0c74d62d482449ebbe38695aee5c06c028b93e736003daa9f66d08bf264020',
                ],
            ],
            'documentos' => [
                'ctx20_dbca0cf975c9_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '2fc82c5c598380643373c1b2c7f5fbc0aac2aeff996541e5cbc879893a490992',
                ],
                'ctx20_dbca0cf975c9_directo_delete' => [
                    'tabla' => 'residentes_documentos',
                    'hash' => '78f590029eaa534b8abb782add42e7f009e6560cd60a97ace3a9d4aead9f1fa4',
                ],
                'ctx20_dbca0cf975c9_directo_insert' => [
                    'tabla' => 'residentes_documentos',
                    'hash' => '623adfc17d10eb3a7fb0c01eef556c519b7747a7028b749f27ec08e3089cae19',
                ],
                'ctx20_dbca0cf975c9_directo_update' => [
                    'tabla' => 'residentes_documentos',
                    'hash' => '1d098f0816f745b0bc1d4dbf2908180a33ad086a9bee7825ac508853fb2a2d28',
                ],
                'ctx20_dbca0cf975c9_episodio' => [
                    'tabla' => 'documentos',
                    'hash' => '9912ea6629e7331b8db8e84a0cd29ca27ee4db4853acdd41194be7c1d52dde91',
                ],
                'ctx20_dbca0cf975c9_insert' => [
                    'tabla' => 'documentos',
                    'hash' => '1a6bfca59736d56a74517e9e116d1fe2ba9773973a529482840ab8a3b543b2d8',
                ],
                'ctx20_dbca0cf975c9_update' => [
                    'tabla' => 'documentos',
                    'hash' => '3e2de978bb3c4b8bc8d8338b466914de03a543eff3e567f4fdf050d8f298365e',
                ],
            ],
            'registros_clinicos' => [
                'ctx20_f84097f9d2d1_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => 'e3dc3f5f8db0645f58667e934b82564c77604608d03fe7944f4f849f6f17d2e5',
                ],
                'ctx20_f84097f9d2d1_directo_delete' => [
                    'tabla' => 'residentes_registros_clinicos',
                    'hash' => 'cb7b945fedfb1888337ae61d954f30c4f0b78e231b8b56ced927baf2e05bf058',
                ],
                'ctx20_f84097f9d2d1_directo_insert' => [
                    'tabla' => 'residentes_registros_clinicos',
                    'hash' => '945aae4ebe23b6b8f237c4c136fc364a54848bfd53c51400b47191c541b29ed8',
                ],
                'ctx20_f84097f9d2d1_directo_update' => [
                    'tabla' => 'residentes_registros_clinicos',
                    'hash' => '520a9c42710b25a311027d9a674ab5b02a83ff9bd6a85270aeb03d76ac916329',
                ],
                'ctx20_f84097f9d2d1_episodio' => [
                    'tabla' => 'registros_clinicos',
                    'hash' => '2b88a9640d69c87c5b625fb340dcecf5614bfd573c3d7c8eeb39d00b5bf3585f',
                ],
                'ctx20_f84097f9d2d1_insert' => [
                    'tabla' => 'registros_clinicos',
                    'hash' => 'e79f32da027079e7d31ef44bb71f4c740103b591e6690ab109842770eae4f0d4',
                ],
                'ctx20_f84097f9d2d1_update' => [
                    'tabla' => 'registros_clinicos',
                    'hash' => '6a5a4287d2013b635d21f539cb20cbe89c385112af708a01d11722fb3561222f',
                ],
            ],
            'registros_textuales_clinicos' => [
                'ctx20_77f089e61e5e_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '6ecd7e333b06c07162478d0d24ad45b6d705b2f60b51998564a781397415ce29',
                ],
                'ctx20_77f089e61e5e_directo_delete' => [
                    'tabla' => 'residentes_registros_textuales_clinicos',
                    'hash' => 'cffebb029e2439206b673496bceb7b790374bf996071ecf52e30290920e762ed',
                ],
                'ctx20_77f089e61e5e_directo_insert' => [
                    'tabla' => 'residentes_registros_textuales_clinicos',
                    'hash' => '1d69671a267af7f440475872b39caa3a850c06a8ded7300d9afd8df8c12431aa',
                ],
                'ctx20_77f089e61e5e_directo_update' => [
                    'tabla' => 'residentes_registros_textuales_clinicos',
                    'hash' => '8a1636c39f3e63cdef2bd8c13b61970eedaaaaf7fa205e6de762a5bc0e6159f8',
                ],
                'ctx20_77f089e61e5e_episodio' => [
                    'tabla' => 'registros_textuales_clinicos',
                    'hash' => '37d850d0451346b91842897c383708abd5a11c0d34085da46b7954aa4c6269bb',
                ],
                'ctx20_77f089e61e5e_insert' => [
                    'tabla' => 'registros_textuales_clinicos',
                    'hash' => '9ca94c43a8ce5b3f56c741fa249293cf99ccb4031f98d08bf799d1bda70daa78',
                ],
                'ctx20_77f089e61e5e_update' => [
                    'tabla' => 'registros_textuales_clinicos',
                    'hash' => 'e9ce039e68a70c1c0f5f870852454e64fc7694857f2b210ef6d612bcf0651bea',
                ],
            ],
            'valoraciones_psicopedagogicas' => [
                'ctx20_a9ab53ee839e_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '56946982d1c732235537100372f1d2ecc22d72182e7f24adbe07d145f96dc103',
                ],
                'ctx20_a9ab53ee839e_directo_delete' => [
                    'tabla' => 'residentes_valoraciones_psicopedagogicas',
                    'hash' => '9fe773a32b57eddb49ad2f315bb2f2ccf595fda69c209bd708724c2f51fe6ccd',
                ],
                'ctx20_a9ab53ee839e_directo_insert' => [
                    'tabla' => 'residentes_valoraciones_psicopedagogicas',
                    'hash' => 'b7317d037433f6be1e1384c13afd02dc5790f7101802a3b6656cd8ff4234f99e',
                ],
                'ctx20_a9ab53ee839e_directo_update' => [
                    'tabla' => 'residentes_valoraciones_psicopedagogicas',
                    'hash' => '51c93c3c899b76d4218fde7bf8cbdde91a9d50146191c4447c219dff69aaf732',
                ],
                'ctx20_a9ab53ee839e_episodio' => [
                    'tabla' => 'valoraciones_psicopedagogicas',
                    'hash' => 'd36fa0cc8242420dd926de1e7355cfd41d7effb79a1f0d8fa5463b9602c30e39',
                ],
                'ctx20_a9ab53ee839e_insert' => [
                    'tabla' => 'valoraciones_psicopedagogicas',
                    'hash' => '53d20438a6254217dc993e6d8f4bdc2a0305125e48589b3485840bf0972bf437',
                ],
                'ctx20_a9ab53ee839e_update' => [
                    'tabla' => 'valoraciones_psicopedagogicas',
                    'hash' => '2e9bde88efa891a5d6cb2ff6865c87cd7d8b974d7a649b72e78be7fcddfd934c',
                ],
            ],
            'valoraciones_petersen_originales' => [
                'ctx20_6354b7fd70f5_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '7ae29ce7c716335cfc9a24f092aee946a39a7097f2d499a1b4b68ab823cd03ea',
                ],
                'ctx20_6354b7fd70f5_directo_delete' => [
                    'tabla' => 'residentes_valoraciones_petersen',
                    'hash' => '96eb44aecba5155c7062e3636c9765f35803c16e9b9e85e2b1630c224afa325f',
                ],
                'ctx20_6354b7fd70f5_directo_insert' => [
                    'tabla' => 'residentes_valoraciones_petersen',
                    'hash' => '35baa6eba02a35e466d8a73907520a640e7ce1bdf6c035e175c741b18ab6e651',
                ],
                'ctx20_6354b7fd70f5_directo_update' => [
                    'tabla' => 'residentes_valoraciones_petersen',
                    'hash' => '5bc17ddd71a1f3ed6f362a35b64537d911c4226b08f7d747008ba66b3f3bc8b6',
                ],
                'ctx20_6354b7fd70f5_episodio' => [
                    'tabla' => 'valoraciones_petersen_originales',
                    'hash' => '6b61b88342fa0ae48585ffade94f09f485a3582c01b6882fc3d9f73d8b82334c',
                ],
                'ctx20_6354b7fd70f5_insert' => [
                    'tabla' => 'valoraciones_petersen_originales',
                    'hash' => '149ee2648543867afed3cc2498d91f69ad1d88a5219bf0be858f8e902c0e2dfd',
                ],
                'ctx20_6354b7fd70f5_update' => [
                    'tabla' => 'valoraciones_petersen_originales',
                    'hash' => '84f0d96262ab0670df33ffd66bd3d2882d4f931c6b9bd2c5b4f353e49e962a63',
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
            'derivaciones' => [
                'ctx20_f889ca2ee034' => [
                    'tabla' => 'derivaciones',
                    'hash' => 'f679ff53877deafea82d551d7ad59a70670c137e6f5ad5f78d8df78187bb56e2',
                    'tipo' => 23,
                    'enlace' => '775eca96b3a437e9f07a20939de90036b306ae4e618da1e1e4e90823d3307b25',
                ],
                'ctx20_f889ca2ee034_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '2b20d41bb06dc2ce3b781d727024467e20fde202ff3a792ccc32efc86ed59ca7',
                    'tipo' => 19,
                    'enlace' => '66fb3157118a9fe4d5c83cfab799e0c26894d4903fef2136565f1db81821f1fa',
                ],
                'ctx20_f889ca2ee034_directo' => [
                    'tabla' => 'residentes_derivaciones',
                    'hash' => '2b874eff3183663a0004d5bfe609bd042985327b67fc5251e0b6980c087a38c0',
                    'tipo' => 31,
                    'enlace' => '37ca1bf5069930bdab22badf2ef3523ab2fe2e0f7a8ba37ee0de19f956574912',
                ],
            ],
            'documentos' => [
                'ctx20_dbca0cf975c9' => [
                    'tabla' => 'documentos',
                    'hash' => 'd98144c3e11bf72aeb7c60ffa7fa23cd8f014727c395489b2ebbd3dffa05fd3f',
                    'tipo' => 23,
                    'enlace' => '4580b468244e16b8aca1c0807977aa6a562401b97f60bc0629826f9e165c730f',
                ],
                'ctx20_dbca0cf975c9_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '7aaca9ee624772f8add53dd5ccf3194678cbef13bd939e060ce3fc2b49fff1cd',
                    'tipo' => 19,
                    'enlace' => '99789cb14065694580451108893ea587f7e4f719989cac12c32a3715965e3a69',
                ],
                'ctx20_dbca0cf975c9_directo' => [
                    'tabla' => 'residentes_documentos',
                    'hash' => '10354187b763471f04668947cc8356e9d0d6bfb36c997d7175be20b0494681b7',
                    'tipo' => 31,
                    'enlace' => '6ca893b0536a542b909c601c2021778a25d2e6179eb441f1ac5ca6c0193b8552',
                ],
            ],
            'registros_clinicos' => [
                'ctx20_f84097f9d2d1' => [
                    'tabla' => 'registros_clinicos',
                    'hash' => '9ae5ce0b734e3065540dec4e4df190d71b6007f5375dda53f1fc6d87b90aa97d',
                    'tipo' => 23,
                    'enlace' => '9aef1f1d0f22045c1dbc83919d6349333bca5cce1ab33db910a4da8e99c7ed72',
                ],
                'ctx20_f84097f9d2d1_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => 'ffef301fd1502f393efa593b85945d6ca12d67b4819b38fe62ee70f12beab094',
                    'tipo' => 19,
                    'enlace' => 'fdc2a9b51e5448c240a7db78f14872750f7b63c6bf55e9407635ebd6f1480957',
                ],
                'ctx20_f84097f9d2d1_directo' => [
                    'tabla' => 'residentes_registros_clinicos',
                    'hash' => 'c872b463b480f6c173d6c0ae2431056ebe46c528b903e15a1d69df6ad891eaf8',
                    'tipo' => 31,
                    'enlace' => '785fe1deb5aada165603a1ba05931dda59067a80cde2799c1925ecd8658afa0b',
                ],
            ],
            'registros_textuales_clinicos' => [
                'ctx20_77f089e61e5e' => [
                    'tabla' => 'registros_textuales_clinicos',
                    'hash' => '55240005c501fcb0065b2e9c7ff6887b33bbb1bf70d944939242df6b24a061f3',
                    'tipo' => 23,
                    'enlace' => '5354314625856288aadb933b51eee176f146f4c1f0f356bb609ef0baaacf7f60',
                ],
                'ctx20_77f089e61e5e_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '0d8fce730bd7fdd1d32f0bbf55b5f4679cc48f87a7729fba71fca766c5c403da',
                    'tipo' => 19,
                    'enlace' => 'a9a4fa59b072f7de291006054d79dd183233c568416f3ab338c3bfcd08c05d98',
                ],
                'ctx20_77f089e61e5e_directo' => [
                    'tabla' => 'residentes_registros_textuales_clinicos',
                    'hash' => 'ddeb1ec76845ad67dab36c12165b51b501d9c944c69169f80445c1c8cdc20c35',
                    'tipo' => 31,
                    'enlace' => '49e9c32a9dfb766c39bead91fb3dfa1201c65b28a0afe8ab88d3e0bb2c610f09',
                ],
            ],
            'valoraciones_psicopedagogicas' => [
                'ctx20_a9ab53ee839e' => [
                    'tabla' => 'valoraciones_psicopedagogicas',
                    'hash' => '0b1c111c7ec1c8263c4775d31c1ccfc2f6f1d8c892bd9b9504cde2a4b24116d3',
                    'tipo' => 23,
                    'enlace' => 'af7616d57aa1fc469c189af0b6a7646a3c64a3408b22d8c0d7bd73803ba9ff1a',
                ],
                'ctx20_a9ab53ee839e_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '702bbe93251cef37d6d466e0a8b7954f501122ec70bea2c055b375b0546f8088',
                    'tipo' => 19,
                    'enlace' => '86e0d931befc395a580733b6257163b2cd8cd7d5bd7857666d1ef06f3ebb176f',
                ],
                'ctx20_a9ab53ee839e_directo' => [
                    'tabla' => 'residentes_valoraciones_psicopedagogicas',
                    'hash' => '5e3bd9603e85252da662831f7fb5527c43d238b189ce026d949de65a7fd0ae8f',
                    'tipo' => 31,
                    'enlace' => 'fda4da42554d0cc695c8c54a2d5772051bc120c7a4df097014a2e3a4449067d2',
                ],
            ],
            'valoraciones_petersen_originales' => [
                'ctx20_6354b7fd70f5' => [
                    'tabla' => 'valoraciones_petersen_originales',
                    'hash' => '34b5b18b31d8d65e52228b44eae40642fbf401bcdf1c5fa1f6af8d3b15d55c88',
                    'tipo' => 23,
                    'enlace' => '5cbe78b0f78fb7f90dba2dcd0e23db2797615132038f28f1dc218b7eb68ea5fc',
                ],
                'ctx20_6354b7fd70f5_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '55921c61fcbdba883f23a98c55a3645054811fbdc70312d33e5f9ee7897dee48',
                    'tipo' => 19,
                    'enlace' => '577864f6cf32ec97491a3c372605a3ff716f506b96c613b0f3242bb99459f2e5',
                ],
                'ctx20_6354b7fd70f5_directo' => [
                    'tabla' => 'residentes_valoraciones_petersen',
                    'hash' => '481fd376396a75ad9771499f230e5387732d2f9d2a57178f950292bd213fb0b5',
                    'tipo' => 31,
                    'enlace' => 'b458eddae8cbb49bafd178bbacbc2aadd84ab93ce96bc3c3520edfed38a010d2',
                ],
            ],
            'documentos_con_documentacion_laboral' => [
                'ctx20_dbca0cf975c9' => [
                    'tabla' => 'documentos',
                    'hash' => '23e40b7d41752c363b442dc62f8dc65f077139a0138fa6e45426dc1e62f851d6',
                    'tipo' => 23,
                    'enlace' => '4580b468244e16b8aca1c0807977aa6a562401b97f60bc0629826f9e165c730f',
                ],
                'ctx20_dbca0cf975c9_atencion' => [
                    'tabla' => 'atenciones',
                    'hash' => '7aaca9ee624772f8add53dd5ccf3194678cbef13bd939e060ce3fc2b49fff1cd',
                    'tipo' => 19,
                    'enlace' => '99789cb14065694580451108893ea587f7e4f719989cac12c32a3715965e3a69',
                ],
                'ctx20_dbca0cf975c9_directo' => [
                    'tabla' => 'residentes_documentos',
                    'hash' => '10354187b763471f04668947cc8356e9d0d6bfb36c997d7175be20b0494681b7',
                    'tipo' => 31,
                    'enlace' => '6ca893b0536a542b909c601c2021778a25d2e6179eb441f1ac5ca6c0193b8552',
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
        $variante = $tabla === 'documentos' && $db->getSchemaBuilder()->hasColumn('documentos', 'cod_usuario_carga') ? 'documentos_con_documentacion_laboral' : $tabla;
        $esperadas = self::HUELLAS[$motor][$variante] ?? null;
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
