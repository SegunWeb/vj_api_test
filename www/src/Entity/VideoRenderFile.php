<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VideoRenderFile
 *
 * @ORM\Entity(repositoryClass="App\Repository\VideoRenderFileRepository")
 * @ORM\Table(name="video_render_file")
 */
class VideoRenderFile
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="file", type="string", length=255)
	 */
	protected $file;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="file_name", type="string", length=255)
	 */
	protected $fileName;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="file_ext", type="string", length=255, nullable=true)
	 */
	protected $fileExt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Video", inversedBy="renderFile")
	 * @ORM\JoinColumn(name="video", nullable=false, referencedColumnName="video_id", onDelete="CASCADE")
	 */
	protected $video;
	
	public function getId(): ?int
	{
		return $this->id;
	}
	
	public function getFile(): ?string
	{
		return $this->file;
	}
	
	public function setFile( string $file ): self
	{
		$this->file = $file;
		
		return $this;
	}
	
	public function getVideo(): ?Video
	{
		return $this->video;
	}
	
	public function setVideo( ?Video $video ): self
	{
		$this->video = $video;
		
		return $this;
	}
	
	public function getFileName(): ?string
	{
		return $this->fileName;
	}
	
	public function setFileName( string $fileName ): self
	{
		$this->fileName = $fileName;
		
		return $this;
	}
	
	public function getFileExt(): ?string
	{
		return $this->fileExt;
	}
	
	public function setFileExt( ?string $fileExt ): self
	{
		$this->fileExt = $fileExt;
		
		return $this;
	}
	
	
}
