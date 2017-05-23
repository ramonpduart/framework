<?php
/**
 * MaiaFW - Copyright (c) Marcus Maia (http://marcusmaia.com.br)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author     Marcus Maia (contato@marcusmaia.com.br)
 * @copyright  Copyright (c) Marcus Maia (http://marcusmaia.com.br)
 * @link       http://maiafw.marcusmaia.com.br MaiaFW
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 *
 * @package MaiaFW\Lib\Type
 * @category Types
 * @version 1.0
 */
class ZipFile  {
	/**
	 * Objeto de ZipArchive que acessa diretamente o arquivo Zip.
	 *
	 * @var ZipArchive
	 */
	private $_zip;

	/**
	 * Cria a instância do arquivo zip baseado no endereço do arquivo. Caso o .zip
	 * ainda não exista ele será criado. O comentário passado opcionalmente no
	 * segundo parâmetro só é atribuído a novos arquivos.
	 *
	 * @param string $file Caminho/Nome do arquivo zip a ser gerado,
	 * @param string $coment Comentário a ser atribuído à novos arquivos (caso o
	 * arquivo já exista ele não é atribuído).
	 */
	public function __construct( $filePath, $comment = false ) {
		$this->zip = new ZipArchive();
		if( file_exists($filePath) ) {
			$this->zip->open($filePath);
		} else {
			$this->zip->open($filePath, ZIPARCHIVE::CREATE);
			if($comment) {
				$this->zip->setArchiveComment($comment);
			}
		}
	}

	/**
     * Fecha o arquivo zip que está sendo manipulado.
     *
     * @return bool True caso o arquivo tenha sido liberado com sucesso.
     */
    public function close() {
		return $this->zip->close();
	}

	/**
	 * Adiciona um novo diretório no arquivo zip (recursivamente incluindo todos os
	 * arquivos). Caso o segundo parametro seja omitido, o diretório não será criado
	 * dentro do arquivo zip e os arquivos serão colocados na raiz do zip. Caso o
	 * segundo parâmetro seja informado o caminho será criado dentro do zip
	 * completamente.
	 *
	 * @param string $path Caminho até o diretório que deverá ser
	 * adicionado.Caso não seja um diretório válido será disparada uma exceção.
	 * @param string $internalPath Caminho dentro do arquivo zip onde será entregue os
	 * aruqivos do diretório.
	 * @return bool True caso todo o diretório tenha sido adicionado com sucesso.
	 */
	public function addDirectory( $path, $internalPath=null) {
		$path = realpath($path);
		if(!is_dir($path))
			throw new FwException('Diretório "' . $path . '" informado não existe ou não é um diretório.');
		if( $internalPath != null ) {
			if( !$this->zip->addEmptyDir($internalPath) ) {
				throw new FwException('Não foi possível criar diretório em arquivo zip');
			}
			$internalPath .= "/";
		}

		$dir = new DirectoryIterator($path);
		foreach( $dir as $file ) {
			if( !$file->isDot() ) {
				$filename = $file->getFilename();
				if( $file->isDir() ) {
					if(!$this->addDirectory( $path . '/' . $filename, $internalPath . $filename) ) {
						throw new FwException( 'Não foi possível adicionar um diretório no arquivo zip.' );
					}
				} elseif ( !$this->addFile( $path . '/' . $filename, $internalPath . $filename ) ) {
					throw new FwException('Não foi possível adicionar um arquivo no zip.');
				}
			}
		}
        return true;
    }

	/**
	 * Adiciona um único arquivo no zip.
	 *
	 * @param string $path_file Caminho até o arquivo que será adicionado.
	 * @param string $destino_interno Caminho dentro do arquivo zip onde deverá ser salvo o arquivo.
	 * @return bool
	 */
	public function addFile($path, $internalPath) {
		if( is_file($path) && $this->zip->addFile($path, $internalPath) ) {
			return true;
		} else {
			throw new FwException( 'Não foi possível adicionar um arquivo no arquivo zip.' );
		}
	}
}